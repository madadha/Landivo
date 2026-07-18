<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    protected static bool $isDiscovered = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('avatar_url')
                ->label('الصورة الشخصية / Profile photo')
                ->image()->avatar()->disk('public')->directory('users/avatars')->imageEditor(),
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            $this->getCurrentPasswordFormComponent(),
        ]);
    }
}
