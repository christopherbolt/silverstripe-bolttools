<?php

// Based on https://github.com/stevie-mayhew/silverstripe-svg
// Adds some mods for writing and caching SVG to a file, using SVG as a template with SSViewer processing, functions for setting CSS

/**
 * Class SVGTemplate
 */
class SVGTemplate extends ViewableData
{
    /**
     * The base path to your SVG location
     *
     * @config
     * @var string
     */
    private static $base_path = 'themes/mytheme/svg/';
	
	// Path to save processed file to
    private static $save_path = 'themes/mytheme/combined/';
	
	// Template vars 
	private static $default_template_vars = array();
	private $template_vars = array();
	
	// Custom CSS (for when using URL() function)
	private $custom_css;
	
	// String for mod
	private $file_mod_string = '';
	
	// Custom save path
	private $custom_save_path;
	
	// Remove style tags (css must be manually added to your stylesheet), applies only for inlined svg
	private static $remove_style_tag = true;

    /**
     * @config
     * @var string
     */
    private static $extension = 'svg';

    /**
     * @config
     * @var array
     */
    private static $default_extra_classes = array();

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $fill;

    /**
     * @var string
     */
    private $stroke;

    /**
     * @var string
     */
    private $width;

    /**
     * @var string
     */
    private $height;

    /**
     * @var string
     */
    private $custom_base_path;

    /**
     * @var array
     */
    private $extraClasses;

    /**
     * @var array
     */
    private $subfolders;

    /**
     * @param string $name
     * @param string $id
     */
    public function __construct($name, $id = '')
    {
        $this->name = $name;
        $this->id = $id;
        $this->extra_classes = $this->stat('default_extra_classes');
        $this->extra_classes[] = 'svg-'.$this->name;
        $this->subfolders = array();
        $this->out = new DOMDocument();
        $this->out->formatOutput = true;
		
		$this->template_vars = $this->stat('default_template_vars');
    }

    /**
     * @param $color
     * @return $this
     */
    public function fill($color)
    {
        $this->fill = $color;
		$this->file_mod_string .= 'f'.$color;
        return $this;
    }

    /**
     * @param $color
     * @return $this
     */
    public function stroke($color)
    {
        $this->stroke = $color;
		$this->file_mod_string .= 's'.$color;
        return $this;
    }

    /**
     * @param $width
     * @return $this
     */
    public function width($width)
    {
        $this->width = $width;
		$this->file_mod_string .= 'w'.$width;
        return $this;
    }

    /**
     * @param $height
     * @return $this
     */
    public function height($height)
    {
        $this->height = $height;
		$this->file_mod_string .= 'h'.$height;
        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function size($width, $height)
    {
        $this->width($width);
        $this->height($height);
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function customBasePath($path)
    {
        $this->custom_base_path = trim($path, DIRECTORY_SEPARATOR);
		$this->file_mod_string .= 'cbp'.$path;
        return $this;
    }
	
	/**
     * @param $class
     * @return $this
     */
    public function customSavePath($path)
    {
        $this->custom_save_path = trim($path, DIRECTORY_SEPARATOR);
		$this->file_mod_string .= 'csp'.$path;
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function extraClass($class)
    {
        $this->extra_classes[] = $class;
		$this->file_mod_string .= 'ec'.$class;
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function addSubfolder($folder)
    {
        $this->subfolders[] = trim($folder, DIRECTORY_SEPARATOR);
		$this->file_mod_string .= 'asf'.$folder;
        return $this;
    }
	
	// set a template variable
	public function SetVar($name, $value) {
		 $this->template_vars[$name] = trim($value);
		 $this->file_mod_string .= 'sv'.$name.$value;
		 return $this;
	}
	
	// set custom CSS
	public function CSS($css) {
		 $this->custom_css .= $css;
		 $this->file_mod_string .= 'c'.$css;
		 return $this;
	}
	
	// set custom CSS
	public function CSSProp($selector, $prop, $value) {
		 $this->custom_css .= $selector.'{'.$prop.':'.$value.';}';
		 $this->file_mod_string .= 'cp'.$selector.$prop.$value;
		 return $this;
	}

    /**
     * @param $filePath
     * @return string
     */
    private function process($filePath, $keepStyleTag=null)
    {

        if (!file_exists($filePath)) {
            return false;
        }
		
		$out = new DOMDocument();
		
		// Parse template before dom level parsing since that may screw it up
		if ($this->template_vars) {
			$data = new ArrayData($this->template_vars);
			$out->loadXML($data->renderWith(SSViewer::fromString(file_get_contents($filePath))));
        } else {
        	$out->load($filePath);
		}

        if (!is_object($out) || !is_object($out->documentElement)) {
            return false;
        }

        $root = $out->documentElement;
		if ($this->extra_classes) {
			$root->setAttribute('class', implode(' ', $this->extra_classes));
		}
		
		if ($this->custom_css) {
            if ($style = $out->getElementsByTagName('style')) {
				$style->item(0)->nodeValue .= $this->custom_css;
			} else {
				$newStyle = $out->createElement('style', $this->custom_css);
				if ($defs = $out->getElementsByTagName('defs')) {
					$defs->item(0)->appendChild( $newStyle );
				} else {
					$svg = $out->getElementsByTagName('svg');
					$svg->item(0)->appendChild('defs')->appendChild($newStyle);
				}
			}
        }
		
		if ($this->fill) {
            $root->setAttribute('fill', $this->fill);
        }

        if ($this->stroke) {
            $root->setAttribute('stroke', $this->stroke);
        }

        if ($this->width) {
            $root->setAttribute('width',  $this->width . 'px');
		}
		
        if ($this->height) {
            $root->setAttribute('height', $this->height . 'px');
        }

        if ($this->extra_classes) {
			$classes = implode(' ', $this->extra_classes);
            $root->setAttribute('class', $classes);
        }
		
		if (!$keepStyleTag && $this->stat('remove_style_tag')) {
			 foreach ($out->getElementsByTagName('style') as $element) {
				 $element->parentNode->removeChild($element);
			 }
		}

        foreach ($out->getElementsByTagName('svg') as $element) {
            if ($this->id) {
                $element->setAttribute('id', $this->id);
            } else {
                if ($element->hasAttribute('id')) {
                    $element->removeAttribute('id');
                }
            }
        }

        $out->normalizeDocument();
        return $out->saveHTML();
    }
	
	private function buildPath() {
		$path = BASE_PATH . DIRECTORY_SEPARATOR;
        $path .= ($this->custom_base_path) ? $this->custom_base_path : $this->stat('base_path');
        $path .= DIRECTORY_SEPARATOR;
        foreach($this->subfolders as $subfolder) {
            $path .= $subfolder . DIRECTORY_SEPARATOR;
        }
        $path .= (strpos($this->name, ".") === false) ? $this->name . '.' . $this->stat('extension') : $this->name;
		
		return $path;	
	}

    /**
     * @return string
     */
    public function forTemplate() {
		$path = $this->buildPath();
        return $this->process($path);
    }
	
	// Writes the modified SVG file to a file and returns the path
	public function URL() {
		$path = $this->buildPath();
		$base = BASE_PATH . DIRECTORY_SEPARATOR;
		$save = ($this->custom_save_path) ? $this->custom_save_path : $this->stat('save_path');
		if (substr($save, strlen($save)-1) != DIRECTORY_SEPARATOR) {
			$save .= DIRECTORY_SEPARATOR;
		}
		//foreach($this->subfolders as $subfolder) {
		//    $path .= $subfolder . DIRECTORY_SEPARATOR;
		//}
		$file_mod_string = preg_replace("/[^a-zA-Z0-9]/smi", '', $this->file_mod_string);
		$file_mod_string .= 'sc'.SiteConfig::current_site_config()->ID;
		$save .= preg_replace("/\.[a-z]+$/smi", '', $this->name) . $file_mod_string . '.' . $this->stat('extension');
		
		if (!file_exists($base.$save)) {
			$xml = $this->process($path, true);
			file_put_contents($base.$save, $xml);
		}
		
		return $save;
	}
}
