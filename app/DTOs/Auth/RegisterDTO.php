<?php

namespace App\DTOs\Auth;

readonly class RegisterDTO
{
    public function __construct(
        public string  $name,
        public string  $email,
        public string  $password,
        public string  $idType,
        public string  $idNumber,
        public string  $phoneNumber,
        public bool    $termsAccepted,
        public ?string $profilePicUrl = null,
    ) {}

    public static function fromArray(array $data, ?string $profilePicUrl = null): self
    {
        return new self(
            name:          $data['name'],
            email:         $data['email'],
            password:      $data['password'],
            idType:        $data['id_type'],
            idNumber:      $data['id_number'],
            phoneNumber:   $data['phone_number'],
            termsAccepted: (bool) $data['terms_accepted'],
            profilePicUrl: $profilePicUrl,
        );
    }
}
