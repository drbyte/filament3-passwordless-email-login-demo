<?php

namespace App\Filament\EmailAuth;

use App\Filament\EmailAuth\Mail\MagicLinkEmail;
use App\Models\Site;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Pages\Auth\Login
{
//    protected static string $view = 'filament.auth-email.login';
// skip Filament's default, and use our own custom view, which contains several parts from Filament's default
    protected static string $view = 'auth.magic-link.login';

    public string $email = '';

    public bool $remember = false;

    public bool $submitted = false;

    protected string $userModel;

    // used only to display onscreen in blade template (the email itself generates the subject directly from the Settings class)
    public string $email_subject = ''; // we tell them on-screen what the email subject line will be, to help them know what to look for

    public string $login_message = 'The first step is to identify yourself by email. Then you will be able to manage and edit all your content.';

    public function form(Form $form): Form
    {
        if (Site::votingIsOpen()) {
            $this->login_message = 'The first step is to identify yourself with your membership email address. We will email you with a link which you can click to login and vote.';
        }
        if (Site::isClosed() {
            $this->login_message = 'Are you sure? Nominating and Voting are closed until next year. Results will be presented at our annual conference.';
        }

        return $form
            ->schema([
                $this->getEmailFormComponent(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        Session::put('email', $data['email']);

        // findOrCreate the model for the user, if appropriate
        $model = app(FilamentPasswordless::class)->getModel($data['email']);

        if (! $model) {
            $this->throwFailureValidationException();
//@TODO - if email isn't found anywhere in our tables, should we explain anything?
//            throw ValidationException::withMessages([
//                'email' => __('filament::login.messages.failed'),
//            ]);
        }

        // if we get here, we're allowed to access but not already logged-in, so send link
        $magicLink = MagicLink::create($model, $data['remember'] ?? true);
        $tryAgain = Filament::getCurrentPanel()->getUrl();

        Mail::to($model->email)->send(new MagicLinkEmail(email: $model->email, magicLink: $magicLink, tryAgainUrl: $tryAgain));

        $this->submitted = true;

        // this is for the Blade template:
        $this->email_subject = 'Login access to SPFA Awards Site';

        return null;
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::pages/auth/register.actions.login.label'))
            ->url(filament()->getLoginUrl());
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }


    protected function getRememberFormComponent(): Component
    {
        return Hidden::make('remember')->default(1)
            ->label(__('filament-panels::pages/auth/login.form.remember.label'));
    }
}
