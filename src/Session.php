<?php

namespace EvLimma\ActionPack;

use EvLimma\ActionPack\Message;

class Session
{
    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public function __get($name)
    {
        if (!empty($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return null;
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function all(): ?object
    {
        return (object)$_SESSION;
    }

    public function set(string $key, $value): Session
    {
        $_SESSION[$key] = (is_array($value) ? (object)$value : $value);
        return $this;
    }

    public function unset(string $key): Session
    {
        unset($_SESSION[$key]);
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function regenerate(): Session
    {
        session_regenerate_id(true);
        return $this;
    }

    public function destroy(): Session
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        return $this;
    }

    public function flash(): ?Message
    {
        if ($this->has("flash")) {
            $flash = $this->flash;
            $this->unset("flash");
            return $flash;
        }

        return null;
    }

    public function csrf(?string $session = null): void
    {
        if (empty($session)) {
            $session = 'csrf_token';
        }

        $_SESSION[$session] = md5(uniqid(rand(), true));
    }
}
