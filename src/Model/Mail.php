<?php

namespace App\Model;

class Mail
{
    private $sender;
    private array $to = [];
    private array $replyTo = [];
    private string $subject;
    private string $html;
    private array $bcc = [];

    public function __construct(array $data = [])
    {
        $this->sender = $data['sender'] ?? [];
        $this->subject = $data['subject'] ?? '';
        $this->html = $data['html'] ?? '';
        $this->to = $data['to'] ?? [];
        $this->bcc = $data['bcc'] ?? [];
    }

    public function getSender(): array
    {
        return $this->sender;
    }

    public function setSender(array $sender): self
    {
        $this->sender = [
            'name' => $sender['name'],
            'email' => $sender['email']
        ];
        return $this;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function getDestinataires(): array
    {
        return $this->to;
    }

    public function addTo(array $destinataire): self
    {
        $this->to[] = [
            'name' => $destinataire['name'],
            'email' => $destinataire['email']
        ];
        return $this;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setbcc(array $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function addBcc(array $destinataire): self
    {
        $this->bcc[] = [
            // 'name' => $destinataire['name'],
            'email' => $destinataire['email']
        ];
        return $this;
    }

    public function addDestinataire(array $destinataire): self
    {
        return $this->addTo($destinataire);
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }


    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): self
    {
        $this->html = $html;
        return $this;
    }

    public function setReplyTo(array $replyTo): self
    {
        $this->replyTo = [
            'name' => $replyTo['name'],
            'email' => $replyTo['email']
        ];
        return $this;
    }

    public function getReplyTo(): array
    {
        return $this->replyTo;
    }
}