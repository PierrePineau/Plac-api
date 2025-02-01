<?php

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticateUser implements UserInterface
{
    private $id;
    private $token;
    public $email;
    private $roles;
    private $site;
    private $expDate;

    public function __construct($token){
        $this->token = $token;
        $tokenInfos = $this->getTokenInfos($token);
        // $this->nom = $tokenInfos['nom'];
        // $this->prenom = $tokenInfos['prenom'];
        $this->id = $tokenInfos['id'];
        $this->site = $tokenInfos['site_code'] ?? null;
        $this->email = $tokenInfos['email'];
        $this->roles = $tokenInfos['roles'];
        $this->expDate = (new \Datetime)->setTimestamp($tokenInfos['exp']);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenInfos($token)
    {
        
        $jwt = $token;
        
        // split the token
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];
        

        $dataPayload = json_decode($payload, true);
        
        // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
        $expiration = (new \Datetime)->setTimestamp($dataPayload['exp']);
        $tokenExpired = (new \Datetime() > $expiration);

        return $dataPayload;
    }
    
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getToken(): string
    {
        return (string) $this->token;
    }

    public function getSiteCode(): string
    {
        if (!isset($this->site['code']) || empty($this->site['code'])) {
            return '';
        }else{
            return (string) $this->site['code'];
        }
    }

    public function getExpDate()
    {
        return $this->expDate;
    }

    public function isCustomer(): bool
    {
        // Pour qu'il soit customer il ne doit posséder que le role ROLE_USER
        return in_array('ROLE_USER', $this->roles) && count($this->roles) === 1;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles) || in_array('ROLE_SUPER_ADMIN', $this->roles);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->roles);
    }

}