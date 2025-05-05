<?php
namespace App\Enums\UserRole;

enum Role:int
{
    case ADMIN = 1;
    case ASSOCIATION = 2;
    case BRAND = 3;
    case WHOLESALER = 4;
    case STORE = 5;
    case MEMBER = 6;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::ASSOCIATION => 'Association',
            self::BRAND => 'Brand',
            self::WHOLESALER => 'Wholesaler',
            self::STORE => 'Store',
            self::MEMBER => 'Member',
        };
    }
}
