<?php

namespace App\Filament\StoreAuth\Pages;

use Illuminate\Support\Facades\Storage;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Validation\Rules\Password;
use Filament\Forms\Components\Wizard\Step;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    protected ?string $maxWidth = '2xl';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Store Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextInput::make('name_store')
                                ->label('Name of Store')
                                ->required()
                                ->maxLength(255)
                                ->autofocus(),

                            TextInput::make('owner_name')
                                ->label('Owner Name')
                                ->required()
                                ->maxLength(255),

                            Textarea::make('address')
                                ->label('Address')
                                ->required()
                                ->autosize(),

                            TextInput::make('phone')
                                ->label('Phone')
                                ->tel()
                                ->required(),

                            TextInput::make('no_rekening')
                                ->label('No Rekening')
                                ->required(),

                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique($this->getUserModel()),
                        ]),

                    Step::make('Password')
                        ->icon('heroicon-o-lock-closed')
                        ->schema([
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->revealable(filament()->arePasswordsRevealable())
                                ->required()
                                ->rule(Password::default())
                                ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                            TextInput::make('passwordConfirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->revealable(filament()->arePasswordsRevealable())
                                ->required()
                                ->dehydrated(false)
                                ->same('password')
                                ->validationAttribute('Confirm Password')
                                ->validationMessages([
                                    'same' => 'The password confirmation field must match password.',
                                ]),
                        ]),

                    Step::make('Store Logo')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            FileUpload::make('logo')
                                ->label('')
                                ->alignCenter()
                                ->placeholder('Upload Your Store Logo')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    null,
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ])
                                ->avatar()
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeMode('cover'),
                        ]),
                ])
                    ->skippable(false)
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(
                        <<<'BLADE'
                    <x-filament::button
                        type="submit"
                        size="sm"
                    >
                        Register
                    </x-filament::button>
                    BLADE
                    )))
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
