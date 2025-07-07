<?php
/**
 * Class Fancine_Skin_Validator
 *
 * Valida y sanea el código PHP y CSS de un skin antes de guardarlo,
 * evitando inyecciones, funciones peligrosas, superglobals, URLs externas, expresiones, etc.
 *
 * @package Fancine_Elementor_Addons_Pro
 */

defined( 'ABSPATH' ) || exit;

class Fancine_Skin_Validator {

    /**
     * Whitelist de funciones PHP permitidas.
     * @var string[]
     */
    protected $allowed_php_functions = [
        'abs', 'ceil', 'floor', 'round',
        'strtoupper', 'strtolower', 'substr', 'strpos', 'str_replace',
        'count', 'in_array', 'array_merge', 'implode', 'explode',
        'json_encode', 'json_decode',
        // …amplía según necesidades del proyecto
    ];

    /**
     * Tokens PHP que jamás deben aparecer.
     * @var int[]
     */
    protected $disallowed_php_tokens = [
        T_EVAL,
        T_EXIT,
        T_INCLUDE,
        T_INCLUDE_ONCE,
        T_REQUIRE,
        T_REQUIRE_ONCE,
        T_BACKTICK,          // ejecuciones con ``
        T_INLINE_HTML,       // HTML fuera de PHP
    ];

    /**
     * Superglobals prohibidos dentro del skin.
     * @var string[]
     */
    protected $disallowed_superglobals = [
        '$_GET', '$_POST', '$_REQUEST',
        '$_COOKIE', '$_FILES', '$_ENV',
        '$_SERVER', '$GLOBALS'
    ];

    /**
     * Whitelist de propiedades CSS permitidas.
     * @var string[]
     */
    protected $allowed_css_properties = [
        'color', 'background-color', 'width', 'height',
        'margin', 'padding', 'font-size', 'font-weight',
        'font-style', 'text-decoration', 'border',
        'border-radius', 'box-shadow', 'opacity',
        'display', 'position', 'top', 'right',
        'bottom', 'left', 'float', 'clear',
        'overflow', 'visibility', 'text-align',
        'vertical-align', 'line-height', 'letter-spacing',
        'word-spacing', 'white-space',
        'background-image', 'background-position',
        'background-size', 'background-repeat',
        // …añade más según el diseño global del plugin
    ];

    /**
     * Whitelist de funciones CSS permitidas.
     * @var string[]
     */
    protected $allowed_css_functions = [
        'rgb', 'rgba', 'hsl', 'hsla', 'url'
    ];

    /**
     * Valida un fragmento de código PHP.
     *
     * @param string $code El código PHP (sin <?php) a validar.
     * @return bool True si pasa todas las comprobaciones.
     */
    public function validate_php_code( $code ) {
        // 1) Normalizar: eliminar comentarios y etiquetas PHP
        $code = preg_replace( '!/\*.*?\*/!s', '', $code );
        $code = preg_replace( '/\/\/.*?[\r\n]/', "\n", $code );
        $code = preg_replace( '/^\s*<\?php\s*/i', '', $code );

        // 2) Tokenizar
        $tokens = token_get_all( "<?php\n{$code}" );

        // 3) Rechazar tokens prohibidos y superglobals
        foreach ( $tokens as $token ) {
            if ( is_array( $token ) ) {
                if ( in_array( $token[0], $this->disallowed_php_tokens, true ) ) {
                    return false;
                }
                if ( T_VARIABLE === $token[0]
                    && in_array( $token[1], $this->disallowed_superglobals, true ) ) {
                    return false;
                }
            }
        }

        // 4) Analizar llamadas a función
        $calls = $this->get_function_calls( $tokens );
        foreach ( $calls as $fn ) {
            $fn_low = strtolower( $fn );
            if ( ! in_array( $fn_low, $this->allowed_php_functions, true ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extrae nombres de funciones llamadas en un token stream.
     *
     * @param array $tokens Resultado de token_get_all().
     * @return string[] Lista de nombres de función llamados.
     */
    protected function get_function_calls( array $tokens ) {
        $calls = [];
        $count = count( $tokens );
        for ( $i = 0; $i < $count - 1; $i++ ) {
            if ( is_array( $tokens[ $i ] )
                && T_STRING === $tokens[ $i ][0 ]
                && '(' === ( is_array( $tokens[ $i + 1 ] ) ? $tokens[ $i + 1 ][1 ] : $tokens[ $i + 1 ] )
            ) {
                $calls[] = $tokens[ $i ][1 ];
            }
        }
        return $calls;
    }

    /**
     * Valida y devuelve sana una cadena CSS.
     *
     * @param string $css Bloque CSS (sin <style>).
     * @return string|false CSS filtrado si pasa, o false si detecta peligro.
     */
    public function sanitize_css( $css ) {
        // 1) Quitar comentarios
        $css = preg_replace( '!/\*.*?\*/!s', '', $css );

        // 2) Separar declaraciones
        $out = '';
        foreach ( explode( ';', $css ) as $decl ) {
            if ( false === strpos( $decl, ':' ) ) {
                continue;
            }
            list( $prop, $value ) = explode( ':', $decl, 2 );
            $prop  = trim( strtolower( $prop ) );
            $value = trim( $value );

            // 3) Validar propiedad
            if ( ! in_array( $prop, $this->allowed_css_properties, true ) ) {
                continue;
            }

            // 4) Prohibir expression() o url externas
            if ( stripos( $value, 'expression(' ) !== false ) {
                continue;
            }
            if ( preg_match( '/url\(\s*[\'"]?(https?:)?\/\//i', $value ) ) {
                continue;
            }

            // 5) Comprobar funciones CSS permitidas
            if ( preg_match_all( '/([a-z0-9_-]+)\s*\(/i', $value, $m ) ) {
                foreach ( $m[1] as $fn ) {
                    if ( ! in_array( strtolower( $fn ), $this->allowed_css_functions, true ) ) {
                        continue 2;
                    }
                }
            }

            $out .= "{$prop}:{$value};";
        }

        return $out;
    }

    /**
     * Validación completa de PHP + CSS de un skin.
     *
     * @param string $php_code Bloque PHP sin <?php
     * @param string $css_code Bloque CSS sin <style>
     * @return bool True si ambos pasan; false en caso contrario.
     */
    public function validate( $php_code, $css_code ) {
        if ( ! $this->validate_php_code( $php_code ) ) {
            return false;
        }
        if ( false === $this->sanitize_css( $css_code ) ) {
            return false;
        }
        return true;
    }
}
