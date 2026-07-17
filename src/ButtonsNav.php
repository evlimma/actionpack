<?php

namespace source\Support;

class ButtonsNav
{
    private string $text;

    public function __toString()
    {
        return $this->render() ?? '';
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function butNavPrevious(string $url): void {
        $this->butNav(
            text: "Voltar",
            urlProtect: url($url),
            ico: "iconevoltar.svg",
            class: "nav_link",
        );
    }

    public function butNavInsert(string $route): void {
        $this->butNav(
            text: "Incluir",
            urlProtect: url($route),
            ico: "iconeincluir.svg",
            class: "nav_link",
        );
    }

    public function butNavSave(object $route): void {
        $this->butNav(
            text: "Salvar",
            urlProtect: $route->name === "read" ? "#link_inactive" : url($route->url),
            linkSpare: "#",
            ico: "iconesalvar.svg",
            class: "nav_link btSalvarGeral",
        );
    }

    public function butNavPdf(string $url): void {
        $this->butNav(
            text: "Visualizar PDF",
            urlProtect: url($url . "/{field_seq}"),
            ico: "icone_visu.svg",
            class: "nav_link btVisualizarPdf",
            targetBlank: true
        );
    }

    public function butNavDel(object $route): void {
        $this->butNav(
            text: "Excluir",
            urlProtect: routesIndex($route->url, "delete") ? url($route->url) : "#link_inactive",
            linkSpare: "#excluirRegistro",
            ico: "icone_excluir_vermelho.svg",
            class: "nav_link_red color_red btExcluirBanco",
            attr: [
                "data-post" => routesIndex($route->url, "delete") ? url($route->path) : "",
                "data-_method" => "DELETE",
                "data-csrf" => csrf_input("csrf_delete", false)
            ],
            classSpan: "color_red disp_resp_none"
        );
    }

    /**
     * $urlProtect = URL segura que está no routes
     */
    public function butNav(
        string $text,
        string $urlProtect = "#",
        ?string $ico = null,
        ?string $class = null,
        ?array $attr = null,
        ?string $classSpan = null,
        ?bool $targetBlank = false,
        bool $buttonSubmit = false,
        ?string $linkSpare = null,
        ?array $params = null
    ): self {
        $attributes = null;
        foreach ($attr ?? [] as $key => $value) {
            $attributes .= $key . "='" . $value . "' ";
        }

        $strTargetBlank = ($targetBlank ? "target='_blank'" : "");
        $urlProtect = str_replace(["[", "]"], ["{", "}"], $urlProtect);
        $href = $linkSpare ? $linkSpare : $urlProtect;

        if ($params) {
            foreach ($params as $key => $value) {
                $href = str_replace(
                    "{" . $key . "}",
                    $value,
                    $href
                );
            }
        }

        $urlFind = extractRight($urlProtect, '#', 1) ?? $urlProtect;
        $methods = ['get', 'post', 'delete'];

        $exists = array_filter(
            $methods,
            fn ($method) => routesIndex($urlFind, $method)
        );

        !$exists && $href = '#link_inactive';

        $tagHtml = !$buttonSubmit ? "
            <a class='{$class}' {$attributes} href='{$href}' {$strTargetBlank}>" . 
                (empty($ico) ? null : "<span><img src='" . theme("/assets/images/wf/{$ico}") . "'></span>") . "
                <span class='name-span {$classSpan}'>{$text}</span>
            </a>
        " 
        : 
        "
            <button type='submit' class='{$class}'>" . 
                (empty($ico) ? null : "<span><img src='" . theme("/assets/images/wf/{$ico}") . "'></span>") . "

                <span class='{$classSpan}'>{$text}</span>
            </button>
        ";

        $this->text .= $tagHtml;
        return $this;
    }

    public function render(): ?string
    {
        return $this->getText();
    }
}
