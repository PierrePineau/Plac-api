<?php

namespace App\Core\Utils;

use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Tools
{
    public function __construct(
        public ValidatorInterface $validator,
        private UuidFactory $uuidFactory,
    )
    {
    }
    
    public function validate($entity): mixed
    {
        $errors = $this->validator->validate($entity);
        return $errors;
    }

    public function generateCode(string $name, array $data = []): string
    {
        // On retire les espaces en remplaçant les espaces par des tirets '-'
        // On met tout en minuscule
        // Tous les accents sont remplacés par des caractères non accentués
        $code = strtolower(
            str_replace(
                [' ', '-', 'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ù', 'û', 'ü', 'ô', 'ö', 'î', 'ï', 'ç'],
                ['_', '_', 'e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'o', 'o', 'i', 'i', 'c'],
                $name
            )
        );
        // Tous les caractères spéciaux sont remplacés par des tirets '-'
        $code = preg_replace('/[^a-z0-9_]/', '-', $code);
        // On remplace les tirets '-' multiples par un seul tiret '-'
        $code = preg_replace('/_+/', '_', $code);
        // On supprime les tirets '-' en début et fin de chaine
        $code = trim($code, '-');

        $usKebabCase = isset($data['useKebabCase']) && $data['useKebabCase'] == true ? true : false;
        if ($usKebabCase) {
            // Si on fait du useKebabCase, on remplace les tirets '_' par des tirets '-'
            $code = str_replace('_', '-', $code);
        }
        return $code;
    }

    public function genererChaineAleatoire($longueur = 10, string $type = 'default')
    {
        switch ($type) {
            case 'num':
                $caracteres = '0123456789';
                break;
            case 'alpha':
                $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'MAJ':
                $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'withSpecial':
                $caracteres = '!@#$%&0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&';
                break;
            default:
                $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < $longueur; $i++) {
            $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
        return $chaineAleatoire;
    }

    public function generateKey(int $length = 20): string
    {
        return $this->genererChaineAleatoire($length, 'withSpecial');
    }
    
    public function generateUuid(): string
    {
        $timestampBased = $this->uuidFactory->timeBased();
        return $timestampBased->toRfc4122();
        // $b = random_bytes(16);
        // $b[6] = chr(ord($b[6]) & 0x0f | 0x40);
        // $b[8] = chr(ord($b[8]) & 0x3f | 0x80);
        // return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }
}
