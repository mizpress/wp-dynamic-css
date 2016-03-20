<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.0
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 */

/**
 * Dynamic CSS Compiler Utility Class
 * 
 * 
 * Dynamic CSS Syntax
 * ------------------
 * <pre>
 * body {color: $body_color;} 
 * </pre>
 * In the above example, the variable $body_color is replaced by a value 
 * that is retrieved by the filter wp_dynamic_css_get_variable_value. The filter 
 * is passed the variable name without the dollar sign, which can be used with
 * get_option() or get_theme_mod() etc.
 */
class DynamicCSSCompiler
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * @var array The list of dynamic styles paths to compile
     */
    private $stylesheets = array();
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance()
    {
        if (null === static::$instance) 
        {
            static::$instance = new static();
            static::$instance->init();
        }
        
        return static::$instance;
    }
    
    /**
     * Initiate the compiler by hooking to wp_print_styles
     */
    public function init()
    {
        add_action( 'wp_print_styles', array( $this, 'print_compiled_style' ) );
    }
    
    /**
     * Add a style path to the pool of styles to be compiled
     * 
     * @param type $path The absolute path to the dynamic style
     */
    public function enqueue_style( $path, $print )
    {
        $this->stylesheets[] = array(
            'path'  => $path,
            'print' => $print
        );
    }
    
    /**
     * Parse all styles in $this->stylesheets and print them if the flag 'print'
     * is set to true
     */
    public function print_compiled_style()
    {
        ob_start();
        foreach( $this->stylesheets as $style ) 
        {
            if( true === $style['print'] )
            {
                include $style;
                echo "\n";
            }
        }
        $css = $this->parse_css( ob_get_clean() );
        include 'style.phtml';
    }
    
    /**
     * Parse the given CSS string by converting the variables to their 
     * corresponding values retrieved by applying the filter 
     * wp_dynamic_css_get_variable_value.
     * 
     * @param string $css A string containing dynamic CSS (pre-compiled CSS with 
     * variables)
     * @uses wp_dynamic_css_get_variable_value filter
     * @return string The compiled CSS after converting the variables to their 
     * corresponding values
     */
    public function parse_css( $css )
    {   
        return preg_replace_callback('#\$([\w]+)#', function($matches) {
            return apply_filters( 'wp_dynamic_css_get_variable_value', $matches[1]);
        }, $css);
    }
}