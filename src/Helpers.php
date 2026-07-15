<?php

/**
 * ####################
 * ###   VALIDATE   ###
 * ####################
 */
function is_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_passwd(string $password): bool
{
    if (password_get_info($password)['algo']) {
        return true;
    }

    return (mb_strlen($password) >= CONF_PASSWD_MIN_LEN && mb_strlen($password) <= CONF_PASSWD_MAX_LEN ? true : false);
}

function is_empty_value($v, $ifEmpty, $notEmpty)
{
    return ($v ?? '') === '' ? $ifEmpty : $notEmpty;
}

function is_empty_custom($var): bool
{
    return in_array($var, [false, "", [], null], true);
}

function passwd(string $password): string
{
    return password_hash($password, CONF_PASSWD_ALGO, CONF_PASSWD_OPTION);
}

function passwd_verify(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function passwd_rehash(string $hash): bool
{
    return password_needs_rehash($hash, CONF_PASSWD_ALGO, CONF_PASSWD_OPTION);
}

function generatePassword(int $length = 16): string
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?';
    $password = '';

    $max = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }

    return $password;
}

function session(): \EvLimma\ActionPack\Session
{
    return new \EvLimma\ActionPack\Session();
}

/**
 * @return string
 */
function csrf_input(?string $session = null, bool $includeInput = true): string
{
    session()->csrf($session);

    if ($includeInput) {
        return "<input type='hidden' name='csrf' value='" . ((empty($session) ? session()->csrf_token : session()->{$session}) ?? "") . "'/>";
    }

    return session()->{$session};
}

/**
 * @param $request
 * @return bool
 */
function csrf_verify($request, ?string $session = null): bool
{
    if (empty($session)) {
        $session = 'csrf_token';
    }

    if (empty(session()->{$session}) || empty($request['csrf']) || $request['csrf'] != session()->{$session}) {
        return false;
    }

    return true;
}

function flash(): ?string
{
    $flash = session()->flash();
    return ($flash) ?? null;
}

function isValidPng(string $filePath): bool
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    if (filesize($filePath) < 100) {
        return false;
    }

    // Checa assinatura PNG
    $fp = @fopen($filePath, 'rb');
    if (!$fp) return false;
    $signature = fread($fp, 8);
    fclose($fp);
    if ($signature !== "\x89PNG\r\n\x1a\n") {
        return false;
    }

    // Confirma imagem válida com tipo PNG
    $info = getimagesize($filePath);
    if ($info === false || $info['mime'] !== 'image/png') {
        return false;
    }

    // Tenta carregar com GD
    try {
        $img = @imagecreatefrompng($filePath);
        if ($img === false) return false;
        imagedestroy($img);
    } catch (Exception $e) {
        return false;
    }

    return true;
}

/**
 * ##################
 * ###   INT   ###
 * ##################
 */
function isBetween(?int $value, int $min, int $max): bool
{
    if (is_null($value)) {
        return false;
    }

    return $value >= $min && $value <= $max;
}

/**
 * ##################
 * ###   STRING   ###
 * ##################
 */
/**
 * @param string $string
 * @return string
 */
function str_slug(string $string): string
{
    $stringFilter = filter_var(mb_strtolower($string), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
    $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

    $slug = str_replace(
        ["-----", "----", "---", "--"],
        "-",
        str_replace(
            " ",
            "-",
            trim(strtr(mb_convert_encoding($stringFilter, 'ISO-8859-1', 'UTF-8'), mb_convert_encoding($formats, 'ISO-8859-1', 'UTF-8'), $replace))
        )
    );
    return $slug;
}

function boolToString(bool $value): string
{
    return $value ? 'true' : 'false';
}

/**
 * Extrai uma parte da string da direita para esquerda a partir de uma ocorrência encontrada.
 *
 * A função procura o caractere informado começando pelo final da string.
 * O parâmetro `$indice` define qual ocorrência será utilizada:
 *
 * - `1` = última ocorrência
 * - `2` = penúltima ocorrência
 * - `3` = antepenúltima ocorrência
 * - etc.
 *
 * Por padrão, o retorno inclui o caractere encontrado.
 * Caso `$hideCaract` seja `true`, o caractere será removido do retorno.
 *
 * @param string|null $string String que será analisada.
 * @param string $caract Caractere utilizado como referência na busca.
 * @param int $indice Índice da ocorrência contando da direita para esquerda.
 * @param bool $hideCaract Define se o caractere encontrado deve ser removido do retorno.
 *
 * @return string|null
 *
 * @example
 * ```php
 * extractRight('/configuracoes/perfil/{field_seq}', '/', 1);
 * // Retorna: '/{field_seq}'
 * ```
 *
 * ```php
 * extractRight('/configuracoes/perfil/{field_seq}', '/', 1, true);
 * // Retorna: '{field_seq}'
 * ```
 */
function extractRight(
    ?string $string,
    string $caract,
    int $indice,
    bool $hideCaract = false
): ?string {
    if (!$string) {
        return null;
    }

    $positions = array_keys(
        array_reverse(
            array_filter(
                str_split($string),
                fn($char) => $char === $caract
            ),
            true
        )
    );

    $posicao = $positions[$indice - 1] ?? null;

    if ($posicao !== null) {
        if ($hideCaract) {
            $posicao++;
        }

        return substr($string, $posicao);
    }

    return null;
}

/**
 * Extrai uma parte da string da esquerda para direita a partir da última posição encontrada da direita para esquerda
 *
 * @param string $string
 * @param string $caract
 * @return string|null
 */
function extractLeft(?string $string, string $caract): ?string
{
    if (!$string) {
        return null;
    }

    $lastSlashPosition = strrpos($string, $caract);
    $result = substr($string, 0, $lastSlashPosition);

    if (!$result) {
        return null;
    }

    return $result;
}

/**
 * Substitui ** no texto por tags <p5> e </p5>, alternando entre elas a cada ocorrência
 *
 * @param [type] $matches
 * @return void
 */
function replaceStars($matches)
{
    static $count = 0;
    $count++;

    return $count % 2 === 1 ? '<h6>' : '</h6>';
}

/**
 * Adiciona "..." no final se o texto for maior que o limite
 *
 * @param string $text
 * @param integer $limit
 * @return string
 */
function limitText(string $text, int $limit = 100): string
{
    if ($limit <= 3) {
        return mb_substr($text, 0, $limit, 'UTF-8');
    }

    return mb_strlen($text, 'UTF-8') > $limit
        ? mb_substr($text, 0, $limit - 3, 'UTF-8') . '...'
        : $text;
}

/**
 * Verifica se uma palavra específica existe dentro de uma frase
 * findWord('azul', 'O carro azul chegou.')
 * 
 * @param string $word
 * @param string|null $phrase
 * @return bool
 */
function findWord(string $word, ?string $phrase): bool
{
    return strpos($phrase ?? '', $word) !== false;
}

/**
 * Substitue '/' por '.' ou vice-versa
 *
 * @param string $string
 * @param boolean $bar
 * @return string
 */
function replaceBar(string $string, bool $bar = true): string
{
    if ($bar) {
        $return = ltrim(str_replace("/", ".", $string), ".");
    } else {
        $return = ltrim(str_replace(".", "/", $string), "/");
    }

    return "/" . $return;
}

/**
 * @param string $string
 * @return string
 */
function str_flat(string $string): string
{
    $stringFilter = filter_var(mb_strtolower($string), FILTER_SANITIZE_SPECIAL_CHARS);

    $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
    $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

    $flat = str_replace(
        " ",
        "",
        trim(strtr(mb_convert_encoding($stringFilter, 'ISO-8859-1', 'UTF-8'), mb_convert_encoding($formats, 'ISO-8859-1', 'UTF-8'), $replace))
    );
    return $flat;
}

/**
 * 
 * @param string $string
 * @return string
 */
function str_accents(?string $string): string
{
    $stringFilter = filter_var(mb_strtolower($string, "utf-8"), FILTER_SANITIZE_SPECIAL_CHARS);

    $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ';
    $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby';

    $flat = trim(strtr($stringFilter, $formats, $replace));
    return $flat;
}

/**
 * @param string $string
 * @return string
 */
function str_filename(string $string): string
{
    $formats = '\/:*?"<>|';
    $replace = '---------';

    $flat = trim(strtr($string, $formats, $replace));
    return $flat;
}

/**
 * 
 * @param string $separator
 * @param array $expressions
 * @return string
 */
function str_concat(string $separator, array $expressions): string
{
    $values = array_diff($expressions, ["", 0, null]);
    return implode($separator, $values) ?? "-";
}

/**
 * Retorna 0 se o parâmetro for diferente de inteiro
 *
 * @param mixed $num
 * @return string
 */
function str_integer(mixed $num): int
{
    return filter_var($num, FILTER_VALIDATE_INT) ?: 0;
}

function str_empty(mixed $objArray, string $field, mixed $empty = null, ?string $field2 = null): mixed
{
    if (is_array($objArray)) {
        $objArray = (object) $objArray;
    }

    if ($field2) {
        return empty($objArray->{$field}->{$field2}) ? $empty : $objArray->{$field}->{$field2};
    }

    return empty($objArray->{$field}) ? $empty : $objArray->{$field};
}

function str_int(string|int|null $strNumeric, mixed $return = 0): int
{
    return is_numeric($strNumeric) ? $strNumeric : $return;
}

function str_studly_case(string $string): string
{
    $stringFilter = str_slug($string);
    $studlyCase = str_replace(
        " ",
        "",
        mb_convert_case(str_replace("-", " ", $stringFilter), MB_CASE_TITLE)
    );

    return $studlyCase;
}

function str_camel_case(string $string): string
{
    return lcfirst(str_studly_case($string));
}

function str_title(string $string): string
{
    return mb_convert_case(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS), MB_CASE_TITLE);
}

function str_limit_words(string $string, int $limit, string $pointer = "..."): string
{
    $string1 = trim(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
    $arrWords = explode(" ", $string1);
    $numWords = count($arrWords);

    if ($numWords < $limit) {
        return $string;
    }

    $words = implode(" ", array_slice($arrWords, 0, $limit));
    return "{$words}{$pointer}";
}

function str_limit_chars(string $string1, int $limit, string $pointer = "..."): string
{
    $string = trim(filter_var($string1, FILTER_SANITIZE_SPECIAL_CHARS));
    if (mb_strlen($string) <= $limit) {
        return $string;
    }

    $chars = mb_substr($string, 0, mb_strrpos(mb_substr($string, 0, $limit), " "));
    return "{$chars}{$pointer}";
}

function convertStringType(string $value)
{
    if (in_array(strtolower($value), ['true', 'false'], true)) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    if (is_numeric($value) && ctype_digit($value)) {
        return (int) $value;
    }

    return $value;
}

/**
 * ###############
 * ###   URL   ###
 * ###############
 */
function url(?string $path = null): string
{
    $path = $path ?? "";
    
    if (preg_match('/^(https?:\/\/|www\.|#)/', $path)) {
        return $path;
    }
    
    return ROOT . "/" . ltrim($path, "/");
}

function url_param(?\Source\Models\Queries\RoutesCross $routesCross, ?array $params = null): string
{
    $path = $routesCross->url ?? "#link_hidden";

    if (preg_match('/^(https?:\/\/|www\.|#)/', $path)) {
        return $path;
    }

    if ($params) {
        foreach ($params as $key => $value) {
            $path = str_replace(
                "{" . $key . "}",
                $value,
                $path
            );
        }
    }

    return ROOT . "/" . ltrim($path, "/");
}

/**
 * Retorna a URI relativa da aplicação removendo a constante ROOT da URL informada.
 *
 * Se nenhuma URL for informada, utiliza automaticamente a URL atual da requisição.
 *
 * @param string|null $url URL completa para conversão.
 * @return string URI relativa da aplicação.
 */
function uri(?string $url = null): string
{
    $currentUrl = $url ?? "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

    return preg_replace(
        '#^' . preg_quote(ROOT, '#') . '#',
        '',
        $currentUrl
    );
}

function theme(?string $path = null, string $theme = CONF_VIEW_ADMIN): string
{
    return ROOT . "/themes/{$theme}" . ($path ? "/" . ltrim($path, '/') : null) . (extension($path) !== "php" ? "?v=" . CONF_VERSION : null);
}

function redirect(string $url): void
{
    header("HTTP/1.1 302 Redirect");
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        header("Location: {$url}");
        exit;
    }

    $location = url($url);
    header("Location: {$location}");
    exit;
}

function themeDir(?string $path = null, string $theme = CONF_VIEW_ADMIN): string
{
    return dirname(__DIR__, 4) . "/themes/{$theme}/" . ($path ? ltrim($path, '/') : null);
}

function appDir(string $path, ?string $base = null): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, dirname(__DIR__, 4) . "/{$base}" . ($base ? "/" : "") . ltrim($path, '/'));
}

function shared(?string $path = null): string
{
    if ($path) {
        return ROOT . "/shared/" . ltrim($path, '/') . "?v=" . CONF_VERSION;
    }

    return ROOT . "/shared";
}

function convertUrlToPath(string $url): ?string
{
    if (strpos($url, ROOT) === 0) {
        $relativePath = substr($url, strlen(ROOT));

        return rtrim(dirname(__DIR__, 4), DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    return null;
}

function replaceUrlPath(string $string, ?array $variavelOriginal = null, ?array $variavelAlterada = null): ?string
{
    if ($variavelOriginal) {
        $string = str_replace($variavelOriginal, $variavelAlterada, $string);
    }

    return str_replace(ROOT, dirname(__DIR__, 4), $string);
}

function svgColor(string $path, string $color): ?string
{
    $filename = themeDir($path);

    if (!file_exists($filename)) {
        return null;
    }

    $img = file_get_contents($filename);
    $imgReplace = str_replace(["#000", "#000000", "black"], $color, $img);
    return "data:image/svg+xml;base64," . base64_encode($imgReplace);
}

function imgPngBase64(string $path): ?string
{
    $filename = themeDir($path);

    if (!file_exists($filename)) {
        return null;
    }

    $img = file_get_contents($filename);
    return "data:image/png;base64," . base64_encode($img);
}

/**
 * Reduz um link, mantendo os primeiros e últimos N caracteres e substituindo o meio por "..."
 *
 * @param string $link
 * @param integer $qtd
 * @return void
 */
function reduzirLink(string $link, int $qtd)
{
    $tamanho = strlen($link);

    if ($tamanho > $qtd * 2) {
        $inicio = substr($link, 0, $qtd);
        $fim = substr($link, -$qtd);
        return $inicio . '...' . $fim;
    }

    return $link;
}

function extension(string $filename): ?string
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return $ext ?: null;
}

function cycleFolder(string $dir, string $extension): array
{
    $arrFiles = [];

    if (is_dir($dir)) {
        $dirFiles = scandir($dir);

        foreach ($dirFiles as $file) {
            $fileFilter = "{$dir}/{$file}";

            if (is_file($fileFilter) && pathinfo($fileFilter)['extension'] === $extension) {
                $arrFiles[] = $fileFilter;
            }
        }

        usort($arrFiles, function (string $a, string $b) {
            $aBase = basename($a);
            $bBase = basename($b);

            $aUnderscore = str_starts_with($aBase, '_');
            $bUnderscore = str_starts_with($bBase, '_');

            if ($aUnderscore !== $bUnderscore) {
                return $aUnderscore ? -1 : 1;
            }

            return strnatcasecmp($aBase, $bBase);
        });
    }

    return $arrFiles;
}

/**
 * ################
 * ###   DATE   ###
 * ################
 */
/**
 * Formata uma data para formato extenso utilizando IntlDateFormatter.
 *
 * @param \DateTime|null $date Data a ser formatada (aceita null e retorna string vazia)
 * @param string $format Padrão ICU de formatação (ex: "eeee, dd 'de' MMMM 'de' yyyy")
 * @param string $timezone Timezone da data (padrão: America/Sao_Paulo)
 * @param string $locale Localização/idioma (padrão: pt_BR)
 *
 * @return string Data formatada conforme o padrão informado
 *
 * @example
 * echo date_extensive(new \DateTime());
 * // quarta-feira, 15 de abril de 2026
 *
 * @example
 * echo date_extensive(new \DateTime('2024-12-25'), "dd/MM/yyyy");
 * // 25/12/2024
 */
function date_extensive(
    ?DateTime $date,
    string $format = "eeee, dd 'de' MMMM 'de' yyyy",
    string $timezone = 'America/Sao_Paulo',
    string $locale = 'pt_BR'
): string {
    if (!$date) {
        return '';
    }

    static $cache = [];

    $key = $locale . '|' . $timezone . '|' . $format;

    if (!isset($cache[$key])) {
        $cache[$key] = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            $timezone,
            IntlDateFormatter::GREGORIAN,
            $format
        );
    }

    return $cache[$key]->format($date);
}

/**
 * @param string $date
 * @param string $format
 * @return string
 */
function date_fmt(?string $date = "now", string $format = "d/m/Y H\hi"): string
{
    return (new DateTime($date))->format($format);
}

function convert_num_to_En(string $value): ?string
{
    return str_replace(",", ".", str_replace(".", "", $value));
}

/**
 * Converte um número inteiro em letra(s) do alfabeto.
 * Ex: 1 => A, 2 => B, 3 => C, 27 => AA
 *
 * @param int|string $number
 * @param bool $uppercase
 * @return string|null
 */
function number_to_letter(int|string $number, bool $uppercase = true): ?string
{
    $number = filter_var($number, FILTER_VALIDATE_INT);

    if ($number === false || $number < 1) {
        return null;
    }

    $result = '';

    while ($number > 0) {
        $number--;
        $result = chr(65 + ($number % 26)) . $result;
        $number = intdiv($number, 26);
    }

    return $uppercase ? $result : mb_strtolower($result);
}

/**
 * 
 * @param string $date
 * @param string $split             Permitido apenas '-' ou '/'
 * @return bool
 */
function is_date(string $date, string $split = "-"): bool
{
    $dateArr = explode($split, $date);

    if (count($dateArr) <> 3) {
        return false;
    }

    if ($split === "-") {
        return checkdate($dateArr[1], $dateArr[2], $dateArr[0]);
    }

    return checkdate($dateArr[1], $dateArr[0], $dateArr[2]);
}

/**
 * @param string $date
 * @return string
 */
function date_fmt_br(string $date = "now", string $format = CONF_DATE_BR): string
{
    return (new DateTime($date))->format($format);
}

/**
 * @param string $date
 * @return string
 */
function date_fmt_app(?string $date = "now", string $format = CONF_DATE_APP): ?string
{
    if (empty(trim($date))) {
        return null;
    }

    if (findWord("/", $date)) {
        $datehour = explode(" ", $date);
        $dateArr = explode("/", $datehour[0]);
        $date = ($dateArr[2] . "-" . $dateArr[1] . "-" . $dateArr[0]) . (!empty($datehour[1]) ? " " . $datehour[1] : null);
    }

    return (new DateTime($date))->format($format);
}

/**
 * 
 * @param string $date
 * @param int $days
 * @return string
 */
function date_sum(string $date, int $days): string
{
    return date('Y-m-d', strtotime('+' . $days . ' days', strtotime($date)));
}

function getAdjustedMonthName(bool $returnObj = false, int $limit = 20): DateTime|string
{
    $today = new DateTime();
    $day = (int)$today->format('d');

    if ($day <= $limit) {
        $today->modify('first day of last month');
    }

    if ($returnObj) {
        return $today;
    }

    $months = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    ];

    $monthNumber = (int)$today->format('n');
    return ucfirst($months[$monthNumber]);
}

function isInAllowedPeriod(int $limit = 20): bool
{
    $today = new DateTime();
    $day = (int)$today->format('j');
    $lastDay = (int)$today->format('t');

    return ($day >= 1 && $day <= $limit) || ($day >= $lastDay - 1);
}

function round_time_minute(string $dateTime): ?string
{
    $dateTime = new \DateTime($dateTime);
    $minute = (int) $dateTime->format('i');
    $hour = (int) $dateTime->format('H');

    if ($minute < 30) {
        $dateTime->setTime($hour, 30);
    } else {
        $dateTime->setTime($hour + 1, 0);
    }

    if ($hour === 0 && $minute === 0) {
        $dateTime->modify('+1 day');
    }

    $result = $dateTime->format('Y-m-d H:i:s');

    return $result;
}

/**
 * 
 * @param string $date
 * @param int $days
 * @return string
 */
function date_subtract(string $date, int $days): string
{
    return date('Y-m-d', strtotime('-' . $days . ' days', strtotime($date)));
}

function formatTimeDifference($startStr, $endStr)
{
    $start = new DateTime($startStr);
    $end = new DateTime($endStr);

    $interval = $start->diff($end);
    $hours = (int) $interval->format('%H');
    $minutes = (int) $interval->format('%i');

    $parts = [];

    if ($hours > 0) {
        $parts[] = "{$hours} " . ($hours === 1 ? "hora" : "horas");
    }

    if ($minutes > 0 || empty($parts)) {
        $parts[] = "{$minutes} " . ($minutes === 1 ? "minuto" : "minutos");
    }

    return implode(' e ', $parts);
}

/**
 * ################
 * ###   CORE   ###
 * ################
 */
/**
 * @return \EvLimma\ComponentBuilder\ComponentBuilder
 */
function containerType(): \EvLimma\ComponentBuilder\ComponentBuilder
{
    return new \EvLimma\ComponentBuilder\ComponentBuilder();
}

/**
 * @return PDO
 */
/*function db(): PDO
{
    return \Source\Core\Connect::getInstance();
}*/

/**
 * @return \Source\Support\Message
 */
function message(): \EvLimma\ActionPack\Message
{
    return new \EvLimma\ActionPack\Message();
}

/**
 * @param string $key
 * @param int $limit
 * @param int $seconds
 * @return bool
 */
function request_limit(string $key, int $limit = 5, int $seconds = 60): bool
{
    $session = new \EvLimma\ActionPack\Session();
    if ($session->has($key) && $session->$key->time >= time() && $session->$key->requests < $limit) {
        $session->set($key, [
            "time" => time() + $seconds,
            "requests" => $session->$key->requests + 1
        ]);
        return false;
    }

    if ($session->has($key) && $session->$key->time >= time() && $session->$key->requests >= $limit) {
        return true;
    }

    $session->set($key, [
        "time" => time() + $seconds,
        "requests" => 1
    ]);

    return false;
}

/**
 * @param string $field
 * @param string $value
 * @return bool
 */
function request_repeat(string $field, string $value): bool
{
    $session = new \EvLimma\ActionPack\Session();
    if ($session->has($field) && $session->$field == $value) {
        return true;
    }

    $session->set($field, $value);
    return false;
}

/**
 * ##################
 * ###   EXTRAS   ###
 * ##################
 */
function left(?string $value, int $count): ?string
{
    if ($value) {
        return substr($value, 0, $count);
    }

    return null;
}

function right(?string $value, int $count): ?string
{
    if ($value) {
        return substr($value, (strlen($value) - $count), strlen($value));
    }

    return null;
}

function comprimirNome(string $inUsuarioCadastro, bool $primeiroNome = false)
{
    $inUsuarioPriNome = explode(" ", $inUsuarioCadastro);

    if ($primeiroNome) {
        return $inUsuarioPriNome[0];
    }

    $pkCount = (is_array($inUsuarioPriNome) ? count($inUsuarioPriNome) : 0);
    if ($pkCount > 2) {
        return $inUsuarioPriNome[0] . " " . Left($inUsuarioPriNome[1], 1) . ". " . $inUsuarioPriNome[$pkCount - 1];
    } else {
        return $inUsuarioCadastro;
    }
}

/**
 * Comprime um nome completo em diferentes formatos.
 *
 * Modos disponíveis:
 * - 'full'          → Nome completo (padrão)
 * - 'first'         → Apenas o primeiro nome
 * - 'first_last'    → Primeiro + último nome
 * - 'short'         → Primeiro + iniciais do(s) nome(s) do meio + último nome
 *
 * @param string $fullName Nome completo
 * @param string $mode Modo de formatação ('full', 'first', 'first_last', 'short')
 *
 * @return string Nome formatado conforme o modo escolhido
 *
 * @example
 * echo compressName("João da Silva Santos");
 * // João da Silva Santos
 *
 * @example
 * echo compressName("João da Silva Santos", "first");
 * // João
 *
 * @example
 * echo compressName("João da Silva Santos", "first_last");
 * // João Santos
 *
 * @example
 * echo compressName("João da Silva Santos", "short");
 * // João d. S. Santos
 */
function compressName(?string $fullName, string $mode = 'first_last'): string
{
    if (empty($fullName)) {
        return '-';
    }

    $fullName = trim(preg_replace('/\s+/', ' ', $fullName));

    if ($fullName === '') {
        return '';
    }

    $parts = explode(' ', $fullName);
    $count = count($parts);

    if ($count === 1) {
        return $parts[0];
    }

    $first = $parts[0];
    $last = $parts[$count - 1];

    switch ($mode) {
        case 'first':
            return $first;

        case 'first_last':
            return $first . ' ' . $last;

        case 'short':
            if ($count <= 2) {
                return $fullName;
            }

            $middle = array_slice($parts, 1, -1);

            $initials = array_map(function ($name) {
                return mb_substr($name, 0, 1) . '.';
            }, $middle);

            return $first . ' ' . implode(' ', $initials) . ' ' . $last;

        case 'full':
        default:
            return $fullName;
    }
}

/**
 * 
 * @param string $nome
 * @return string
 */
function saudacao(string $nome = ""): string
{
    $hora = date('H');
    if ($hora >= 6 && $hora <= 12) {
        return 'bom dia' . (empty($nome) ? '' : ', ' . $nome);
    } else if ($hora > 12 && $hora <= 18) {
        return 'boa tarde' . (empty($nome) ? '' : ', ' . $nome);
    } else {
        return 'boa noite' . (empty($nome) ? '' : ', ' . $nome);
    }
}

/**
 * 
 * @param string|null $menuSelect
 * @param string $posicaoMenu
 * @return string
 */
function funAtual(?string $menuSelect, string $posicaoMenu): string
{
    if ($menuSelect === $posicaoMenu) {
        return "Atual";
    }

    return "";
}

/**
 * 
 * @param string $nomeTBL
 * @param string $nomeCampos
 * @param array $arrayFiltro
 * @return string
 */
function funSqlSelect(string $nomeTBL, string $nomeCampos, array $arrayFiltro, bool $negation = false): string
{
    $filtros = [];

    $arrayFiltro = array_map(fn($item) => isset($item[2]) ? $item : [$item[0], $item[1], false], $arrayFiltro);

    foreach ($arrayFiltro as [$campo, $valor, $negation]) {
        $filterType = $negation ? ["NOT", "<>"] : ["", "="];

        if ($valor === 'null' || $valor === null) {
            $filtros[] = "{$campo} IS {$filterType[0]} NULL";
        } else {
            $filtros[] = "{$campo} {$filterType[1]} '" . addslashes($valor) . "'";
        }
    }

    $whereClause = !empty($filtros) ? " WHERE " . implode(" AND ", $filtros) : "";

    return sprintf("SELECT %s FROM %s%s", $nomeCampos, $nomeTBL, $whereClause);
}


/**
 * 
 * @param string $nomeTBL
 * @param array $arrayCamposValor
 * @param int $posicaoValor
 * @return string
 */
function funSqlInsert(string $nomeTBL, array $arrayCamposValor, int $posicaoValor = 1): string
{
    $nomeCampos = null;
    $nomeValores = null;

    for ($i = 0; $i < count($arrayCamposValor); $i++) {
        $nomeCampos .= ", " . $arrayCamposValor[$i][0];

        if ($arrayCamposValor[$i][$posicaoValor] === 'null' or $arrayCamposValor[$i][$posicaoValor] === null) {
            $nomeValores .= ", NULL";
        } else {
            $nomeValores .= ", '" . $arrayCamposValor[$i][$posicaoValor] . "'";
        }
    }

    $nomeCampos1 = substr($nomeCampos, 2, strlen($nomeCampos));
    $nomeValores1 = substr($nomeValores, 2, strlen($nomeValores));

    $sql = "INSERT INTO $nomeTBL ($nomeCampos1) VALUES ($nomeValores1);";

    return $sql;
}

/**
 * 
 * @param string $strHoraMin
 * @return float
 */
function funConverteHoraNum(string $strHoraMin): float
{
    $arrHoraMin = explode(":", $strHoraMin);

    $horaDecimal = intval($arrHoraMin[0]);
    $minDecimal = (intval($arrHoraMin[1]) / 60) * 100;

    return floatval($horaDecimal . "." . $minDecimal);
}

/**
 * 
 * @param float $strNum
 * @return string
 */
function funConverteNumHora(float $strNum): string
{
    $arrHoraMin = explode(".", number_format($strNum, 2, '.', ''));
    $horaDecimal = str_pad($arrHoraMin[0], 2, '0', STR_PAD_LEFT);
    $minDecimal = str_pad(intval(($arrHoraMin[1] * 60) / 100), 2, '0', STR_PAD_LEFT);

    return $horaDecimal . ":" . $minDecimal;
}

/**
 * 
 * @param string $strNum
 * @return string
 */
function convertValorInPt(float $strNum, int $decimais = 2, bool $simbolo = false): string
{
    return ($simbolo ? "R$ " : "") . number_format($strNum, $decimais, ',', '.');
}

function convertValorPtIn(string $strNum): float
{
    $value = str_replace(".", "", $strNum);
    $value1 = floatval(str_replace(",", ".", $value));

    return $value1;
}

/**
 * 
 * @param string $percorrerData
 * @return string
 */
function funVerificaFimSemana(string $percorrerData): string
{
    $diaSemanaNum = intval(date("w", strtotime($percorrerData))); // Formato Segunda = 1

    if ($diaSemanaNum === 6) {
        return date_subtract($percorrerData, 1);
    } else if ($diaSemanaNum === 0) {
        return date_subtract($percorrerData, 2);
    } else {
        return $percorrerData;
    }
}

/**
 * 
 * @param float|null $num
 * @param int $decimals
 * @param string|null $decimal_separator
 * @param string|null $thousands_separator
 * @return string|null
 */
function number_fmt(?float $num, int $decimals = 0, ?string $decimal_separator = ".", ?string $thousands_separator = ","): ?string
{
    return $num ? number_format($num, $decimals, $decimal_separator, $thousands_separator) : null;
}

/**
 * 
 * @param float|null $num
 * @return bool
 */
function number_negative(?float $num): bool
{
    return $num < 0 ? true : false;
}

/**
 * 
 * @param float|null $num
 * @return bool
 */
function number_positive(?float $num): bool
{
    return $num > 0 ? true : false;
}

function pluralize(int $count): array
{
    $convertions = [
        ["s", "Foram"],
        ["", "Foi"],
    ];

    return $convertions[$count === 1 ? 1 : 0];
}

function str_pluralize(int $count, string $string, bool $full = true): ?string
{
    return "{$count} {$string}" . (($count === 1) ? null : "s");
}

function is_difference(array $values): bool
{
    foreach ($values as $value) {
        if ($value[0] != $value[1]) {
            return true;
        }
    }

    return false;
}

function is_diff_array(?object $findCod, array $values): bool
{
    foreach ($values as $value) {
        if (str_empty($findCod, $value[0]) != $value[1]) {
            return true;
        }
    }

    return false;
}

function formatPhoneNumber($phone)
{
    $cleaned = preg_replace('/\D/', '', $phone);

    if (strlen($cleaned) === 11) {
        return '(' . substr($cleaned, 0, 2) . ') ' . substr($cleaned, 2, 5) . '-' . substr($cleaned, 7);
    }

    if (strlen($cleaned) === 10) {
        return '(' . substr($cleaned, 0, 2) . ') ' . substr($cleaned, 2, 4) . '-' . substr($cleaned, 6);
    }

    return  $phone;
}

function replacesQueries(string $origem, array $variaveisValores): string
{
    $texto = $origem;

    foreach ($variaveisValores as $variavel => $valor) {
        $texto = str_replace($variavel, "'" . $valor . "'", $texto);
    }

    return $texto;
}

// Exemplo de URL com múltiplos parâmetros
// $urlNavegador = "https://www.youtube.com/watch?v=yGQ80RYx8pc&ab_channel=TreelissProfissional";
// $params = [
//     'controls' => 0,
//     'autoplay' => 1,
//     'mute' => 1,
// ];

// $urlIframe = gerarIframeYoutube($urlNavegador, $params);

// echo $urlIframe;
// Saída: https://www.youtube.com/embed/yGQ80RYx8pc?controls=0&autoplay=1&mute=1
function gerarIframeYoutube($url, $params = []): ?array
{
    if (preg_match('/v=([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $videoID = $matches[1];
        $baseURL = "https://www.youtube.com/embed/{$videoID}";

        if (!empty($params)) {
            $queryString = http_build_query($params);
            $baseURL .= "?$queryString";
        }

        return [$baseURL, "https://img.youtube.com/vi/{$videoID}/0.jpg"];
    }

    return null;
}

/**
 * Cria um array a partir dos valores de um campo de uma coleção de registros.
 * Se `$fillKeys` for `false`, retorna apenas um array indexado contendo os valores do campo.
 *
 * Exemplo:
 * ```php
 * $profileAccessIds = arrayToFields(
 *     (new RoutesPlan())->listEntity(),
 *     'rout_id'
 * );
 *
 * // Resultado:
 * // [
 * //     1 => true,
 * //     2 => true,
 * //     3 => true
 * // ]
 *
 * $routeIds = arrayToFields(
 *     (new RoutesPlan())->listEntity(),
 *     'rout_id',
 *     false
 * );
 *
 * // Resultado:
 * // [1, 2, 3]
 * ```
 *
 * @param array|null $items Array de registros.
 * @param string $field Nome do campo que será extraído.
 * @param bool $fillKeys Define se os valores do campo serão utilizados como
 * chaves do array (`true`) ou se será retornado um array indexado (`false`).
 *
 * @return array Array contendo os valores do campo.
 */
function arrayToFields(
    ?array $items,
    string $field,
    bool $fillKeys = true
): array {
    $fields = array_filter(array_column($items ?? [], $field));

    if (!$fillKeys) {
        return $fields;
    }

    return array_fill_keys($fields, true);
}
