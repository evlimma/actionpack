<?php

namespace EvLimma\ActionPack;

use EvLimma\ActionPack\Session;

class Message {
    private $text;
    private $type;
    private $before;
    private $after;

    public function __toString() {
        return $this->render();
    }

    public function getText(): ?string {
        return $this->before . $this->text . $this->after;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function before(string $text): self {
        $this->before = $text;
        return $this;
    }

    public function after(string $text): self {
        $this->after = $text;
        return $this;
    }

    public function success(string $message, bool $simple = false): self {
        $this->type = ($simple) ? "" : "Aviso|";
        //$this->type = "success icon-check-square-o";
        $this->text = $this->filter($message);
        return $this;
    }

    public function info(string $message, bool $simple = false): self {
        $this->type = ($simple) ? "" : "Info|";
        //$this->type = "info icon-info";
        $this->text = $this->filter($message);
        return $this;
    }

    public function warning(string $message, bool $simple = false): self {
        $this->type = ($simple) ? "" : "Erro|";
        //$this->type = "warning icon-warning";
        $this->text = $this->filter($message);
        return $this;
    }

    public function error(string $message, bool $simple = false): self {
        $this->type = ($simple) ? "" : "Erro|";
        //$this->type = "error icon-warning";
        $this->text = $this->filter($message);
        return $this;
    }

    public function render(): string {
        return $this->getType() . $this->getText();
    }

    public function flash(): void {
        (new Session())->set("flash", $this);
    }

    private function filter(string $message): string {
        return strip_tags($message, '<br>');
        //return htmlspecialchars($message);
    }

}
