<?php

namespace IastateTheme;

use InvalidArgumentException;
use RuntimeException;

/**
 * @link http://www.sample.iastate.edu/
 */
class Theme
{
	const VERSION = '1.4.44';

	/**
	 * Configuration options used by the theme.
	 *
	 * @var array
	 * @link http://www.sample.iastate.edu/download/php/opts/
	 */
	protected $options = array();

	/**
	 * Creates a new theme object.
	 *
	 * @param array|string $pageTitleOrOptions Either the page_title or an options array
	 * @param array $options (optional) Array of options if first argument is page_title
	 */
	public function __construct($pageTitleOrOptions = null, $options = array())
	{
		$this->configure();
		$this->init();
		if (!is_array($pageTitleOrOptions))
		{
			$options['page_title'] = $pageTitleOrOptions;
		}
		else
		{
			$options = $pageTitleOrOptions;
		}
		if ($options)
		{
			$this->setOptions($options);
		}
	}

	/**
	 * Set up default theme configurations.
	 *
	 * @return Theme
	 */
	protected function configure()
	{
		$this->setOptions(array(
			'show_header'       => true,
			'show_top_strip'    => true,
			'show_ribbon'       => true,
			'show_search_box'   => true,
			'show_site_tagline' => false,
			'show_site_title'   => true,
			'show_navbar'       => false,
			'show_sidebar'      => true,
			'show_page_title'   => true,
			'show_footer'       => true,

			'search_action'      => '//google.iastate.edu/search',
			'search_client'      => 'default_frontend',
			'search_output'      => 'xml_no_dtd',
			'search_placeholder' => 'Search',
			'search_site'        => null,
			'search_submit'      => 'Search',
			'search_style'       => 'default_frontend',

			'request_uri'            => null,
			'navbar'                 => null,
			'sidebar'                => null,
			'authorization_callback' => null,
			'route_callback'         => null,
			'translator_callback'    => null,

			'responsive' => true,
			'responsive_disable_large' => false,
			'legacy_html_id_support' => true,
			'legacy_html_class_support' => true,

			'head_title' => function(Theme $theme)
			{
				$titles = array();
				foreach (array('page', 'site', 'org') as $type)
				{
					if (($part = $theme->getOption($type .'_title')) !== null)
					{
						if (count($titles) && $titles[count($titles) - 1] == $part)
						{
							continue;
						}
						$titles[] = $part;
					}
				}
				return $titles;
			},
			'title_separator'  => ' - ',

			'org_title'        => 'Iowa State University',
			'org_url'          => 'http://www.iastate.edu/',

			'site_title'       => null,
			'site_url'         => '',

			'site_tagline'     => null,
			'site_tagline_url' => null,

			'after_ribbon'     => '',
			'after_sidebar'    => '',

			'anchor_image'     => null,

			'page_title'       => null,

			'before_footer'    => '',
			'page_footer'      => '<p>Ames, Iowa 50011, (515) 294-4111, <a href="http://www.iastate.edu/contact/">Contact Us</a>.</p>',
			'footer_copyright' => '<p>Copyright &copy; 1995-{{year}}, Iowa State University of Science and Technology. All rights reserved.</p>',
			'after_footer'     => '',

			'base_path' => '',
			'asset_path' => '',
			'module_asset_path' => '',
			'theme_asset_path' => '',
			'sprite' => '{{theme_asset_path}}/img/sprite.png?v='. self::VERSION,
			'head_meta' => array(
				'charset' => 'utf-8',
				'x_ua_compatible' => array(
					'content' => 'IE=edge,chrome=1',
					'key_value' => 'X-UA-Compatible',
					'key_type' => 'http-equiv',
				),
				'viewport' => function(Theme $theme)
				{
					if ($theme->getOption('responsive'))
					{
						return 'width=device-width,initial-scale=1';
					}
					return null;
				},
			),
			'head_link' => array(
				'base' => array(
					'href' => '{{theme_asset_path}}/css/base.css?v='. self::VERSION,
					'order' => 0,
				),
				'favicon' => array(
					'rel' => 'icon',
					'type' => 'image/x-icon',
					'href' => '{{theme_asset_path}}/favicon.ico?v='. self::VERSION,
				),
				'font-awesome' => '//netdna.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css',
                                'footer' => array(
                                        'href' => '{{theme_asset_path}}/css/footer.css?v='. self::VERSION,
                                        'order' => 1,
                                ),
			),
			'head_style' => array(),
			'head_script' => array(
				'file' => array(),
				'script' => array(
					'html5_shiv' => "/* html5shiv */ (function(){var t='abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video'.split(' ');for(var i=t.length;i--;)document.createElement(t[i])})();",
				),
			),
			'inline_script' => array(
				'file' => array(
					'bootstrapjs' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
				),
				'script' => array(
					'box_sizing' => "/* Grid gutter fix for IE7 */ (function(b){if((' '+b.body.parentNode.className+' ').indexOf(' lt-ie8 ')==-1)return;var d=b.createStyleSheet();var e=function(s){var a=b.all,l=[],i,j;d.addRule(s,'k:v');for(j=a.length;j--;)a[j].currentStyle.k&&l.push(a[j]);d.removeRule(0);return l};var g=e('.grid');for(var i=g.length;i--;)g[i].style.width=g[i].offsetWidth-20})(document);",
					'responsive' => function(Theme $theme)
					{
						if ($theme->getOption('responsive'))
						{
							return "/* Responsive design */ (function(f){if(!f.querySelector||!f.addEventListener)return;var g='add',cc='contains',cr='remove',ct='toggle',sa='active',sb='wd-p-OffCanvasBar',sn='wd-p-OffCanvasNav',ss='wd-p-OffCanvasSearch',ip='.wd-l-SearchBox input[type=\"text\"]',ed='preventDefault',q=function(a,b){return(b||f).querySelectorAll(a)},c=function(a,b,c){b=b||cr;c=c||f.body;a=' '+a+' ';var d=' '+c.className.trim()+' ';var e=d.indexOf(a)!=-1;if(b==cc)return e;else if(!e&&(b==ct||b==g))c.className=d+a;else if(e&&(b==ct||b==cr))c.className=d.replace(a,' ')},h=function(b,c,d){for(var e=q(b),i=e.length;i--;){if(e[i].tagName!='A'){if(!d)e[i].addEventListener('click',function(a){a[ed]()},0);e[i].addEventListener('ontouchstart'in window?'touchstart':'mousedown',c,0)}}},m=q('.wd-l-TopStripMenu-toolbar'),md=function(){q(ip)[0]&&q(ip)[0].blur();for(var i=m.length;i--;)c(sa,cr,m[i])};h('.wd-ResponsiveToggles-nav',function(a){md();c(sb);c(ss);c(sn,ct);a[ed]()});h('.wd-ResponsiveToggles-search',function(a){md();c(sb);c(ss,ct);if(c(ss,cc))q(ip)[0]&&q(ip)[0].focus();c(sn);a[ed]()});h('.wd-l-TopStripMenu-header',function(a){var b=q('.wd-l-TopStripMenu-toolbar',this.parentNode)[0];if(c(sa,cc,b)){c(sa,cr,b);c(sb)}else{md();c(sa,g,b);c(sb,g);c(ss)}a[ed]()});h('.wd-l-Content',function(a){if(c(sn,cc))a[ed]();c(sn)},true)})(document);";
						}
						return null;
					},
				),
			),

			'render_tags' => array(
				'email' => function($str, Theme $theme)
				{
					list(, $email, $label) = array_pad(explode('|', $str), 3, null);
					return $theme->email($email, $label);
				},
			),
		));
	}

	/**
	 * Place theme specific defaults here when extending Theme for sub-sites. This code is called at the beginning
	 * of __construct and can be overwritten by options provided to the constructor.
	 *
	 * @return Theme
	 */
	protected function init()
	{
		return $this;
	}

	/**
	 * Convert an option name from camelCase to under_score. Legacy support.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function inflectOptionName($name)
	{
		if (strtolower($name) != $name)
		{
			$name = preg_replace_callback('/[A-Z]/', function($m)
			{
				return '_'. strtolower($m[0]);
			}, $name);
		}
		return $name;
	}

	/**
	 * Check to see if theme has option set
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasOption($name)
	{
		return array_key_exists($this->inflectOptionName($name), $this->options);
	}

	/**
	 * Get an option. Optionally return a pre-set default value in case option is not set or is null.
	 *
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function getOption($name, $default = null)
	{
		$name = $this->inflectOptionName($name);
		if (isset($this->options[$name]))
		{
			return $this->options[$name];
		}
		return $default;
	}

	/**
	 * Set an option. Will merge value with existing by default if value is an array.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param bool $reset replace existing value if true instead of merging
	 *
	 * @link http://www.sample.iastate.edu/download/php/opts/
	 * @return Theme
	 */
	public function setOption($name, $value, $reset = false)
	{
		$name = $this->inflectOptionName($name);
		if (!$reset && isset($this->options[$name]) && is_array($this->options[$name]) && is_array($value))
		{
			$value = $this->merge($this->options[$name], $value);
		}
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * Get all options (for debugging).
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Set multiple options.
	 *
	 * @param array $options
	 *
	 * @link http://www.sample.iastate.edu/download/php/opts/
	 * @return Theme
	 */
	public function setOptions(array $options)
	{
		if (!empty($options))
		{
			foreach ($options as $name => $value)
			{
				$this->setOption($name, $value);
			}
		}
		return $this;
	}

	/**
	 * Merge two arrays recursively.
	 *
	 * If an integer key exists in both arrays, the value from the second array
	 * will be appended the the first array. If both values are arrays, they
	 * are merged together, else the value of the second array overwrites the
	 * one of the first array.
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return array
	 */
	protected function merge(array $a, array $b)
	{
		foreach ($b as $key => $value)
		{
			if (array_key_exists($key, $a))
			{
				if (is_int($key))
				{
					$a[] = $value;
				}
				elseif (is_array($value) && is_array($a[$key]))
				{
					$a[$key] = $this->merge($a[$key], $value);
				}
				else
				{
					$a[$key] = $value;
				}
			}
			else
			{
				$a[$key] = $value;
			}
		}
		return $a;
	}

	/**
	 * Add a style asset to be rendered within the <head> element. Can be a url or inline style content.
	 *
	 * @param array|string $spec Either a url or inline style (do NOT include <style> tags). Can also be an array:
	 *     - if $mode is 'link'
	 *         - an array of html element attributes (href, media, rel, etc.) and order
	 *     - if $mode is 'style'
	 *         - content: the inline style content (do NOT include <style> tags)
	 *         - attributes: an array of html element attributes
	 * @param string $mode Either 'link' for urls or 'style' for inline styles
	 *
	 * @return Theme
	 * @throws InvalidArgumentException if incorrect $mode provided
	 */
	public function addStyle($spec, $mode = 'link')
	{
		if (is_string($spec) && strlen(trim($spec)) == 0)
		{
			return $this;
		}
		if ($mode != 'link' && $mode != 'style')
		{
			throw new InvalidArgumentException(sprintf(
				"Expected \$mode to be 'link' or 'style', got '%s' instead"
				, $mode
			));
		}
		$this->setOption('head_' . $mode, array(
			md5(serialize($spec)) => $spec,
		));
		return $this;
	}

	/**
	 * Add a script asset to be rendered within the <head> element or within the <body> element. Can be a url
	 * or inline script content.
	 *
	 * @param array|string $spec Either a url or inline script (do NOT include <script> tags). Can also be an array:
	 *     - if $mode is 'file'
	 *         - an array of html element attributes (src, type, etc.) and order
	 *     - if $mode is 'script'
	 *         - content: the inline script content (do NOT include <script> tags)
	 *         - attributes: an array of html element attributes
	 * @param string $mode Either 'file' for urls or 'script' for inline scripts
	 * @param string $position Either 'head' to place inside <head> or 'inline' to append to <body>
	 *
	 * @return Theme
	 * @throws InvalidArgumentException if incorrect $mode or $position is provided
	 */
	public function addScript($spec, $mode = 'file', $position = 'inline')
	{
		if (is_string($spec) && strlen(trim($spec)) == 0)
		{
			return $this;
		}
		if ($mode != 'file' && $mode != 'script')
		{
			throw new InvalidArgumentException(sprintf(
				"Expected \$mode to be 'file' or 'script', got '%s' instead"
				, $mode
			));
		}
		if ($position != 'head' && $position != 'inline')
		{
			throw new InvalidArgumentException(sprintf(
				"Expected \$position to be 'head' or 'inline', got '%s' instead"
				, $position
			));
		}
		$this->setOption($position . '_script', array(
			$mode => array(
				md5(serialize($spec)) => $spec,
			),
		));
		return $this;
	}

	/**
	 * Draws the header (html before the content up to and including the page title)
	 */
	public function drawHeader()
	{
		echo $this->renderHtmlStart();
		echo $this->renderHead();
		echo $this->renderBodyStart();
		echo $this->renderPageStart();
		echo $this->renderSkipNavigation();
		echo $this->renderHeader();
		echo $this->renderContainerStart();
		echo $this->renderSidebar();
		echo $this->renderContentStart();
		echo $this->renderAnchorImage();
		echo $this->renderPageTitle();
		return $this;
	}

	/**
	 * Draws the header (html before the content up to and including the page title)
	 */
	public function drawFrontPage()
	{
		echo $this->renderHtmlStart();
		echo $this->renderHead();
		echo $this->renderBodyStart();
		echo $this->renderPageStart();
		echo $this->renderSkipNavigation();
		echo $this->renderHeader();
		echo $this->renderFrontStart();
		echo $this->renderSidebar();
		echo $this->renderContentStart();
		echo $this->renderAnchorImage();
		echo $this->renderPageTitle();
		return $this;
	}

	/**
	 * Draws the footer (html after the content)
	 */
	public function drawFooter()
	{
		echo $this->renderContentEnd();
		echo $this->renderContainerEnd();
		echo $this->renderFooter();
		echo $this->renderLoadingBar();
		echo $this->renderPageEnd();
		echo $this->renderInlineScript();
		echo $this->renderBodyEnd();
		echo $this->renderHtmlEnd();
		return $this;
	}

	/**
	 * Render doctype and opening <html> tag. Applies conditional .lt-ieXY classes.
	 *
	 * @return string
	 */
	public function renderHtmlStart()
	{
		return <<<HTML
<!DOCTYPE html>
<!--[if lte IE 7]>     <html lang="en-us" class="lt-ie10 lt-ie9 lt-ie8">  <![endif]-->
<!--[if IE 8]>         <html lang="en-us" class="lt-ie10 lt-ie9">         <![endif]-->
<!--[if IE 9]>         <html lang="en-us" class="lt-ie10">                <![endif]-->
<!--[if gt IE 9]><!--> <html lang="en-us">                                <!--<![endif]-->
HTML;
	}

	/**
	 * Render the <head> tag and all items inside of it. Each item is sorted
	 * in its own category if an 'order' key is provided in the spec.
	 *
	 * @return string
	 */
	public function renderHead()
	{
		return <<<HTML
<head>
	{$this->renderHeadMeta()}
	{$this->renderHeadTitle()}
	{$this->renderHeadLink()}
	{$this->renderHeadStyle()}
	{$this->renderHeadScript()}
</head>
HTML;
	}

	/**
	 * Render all <meta> tags.
	 *
	 * @return string
	 */
	public function renderHeadMeta()
	{
		$items = $this->getOption('head_meta', array());
		$index = $this->sort($items);
		$html = array();
		foreach ($index as $name)
		{
			$spec = $items[$name];
			if (is_callable($spec))
			{
				$spec = call_user_func($spec, $this);
			}
			if (is_null($spec))
			{
				continue;
			}
			if (is_string($spec))
			{
				if ($name == 'charset')
				{
					$attr = array(
						'charset' => $spec,
					);
				}
				else
				{
					$attr = array(
						'content' => $spec,
						'name' => $name,
					);
				}
			}
			else
			{
				$attr = array(
					'content' => $spec['content'],
					$spec['key_type'] => $spec['key_value'],
				);
			}
			$html[] = '<meta ' . $this->createAttributesString($attr) . '>';
		}
		return implode("\n", $html);
	}

	/**
	 * Render the <title> tag.
	 *
	 * @return string
	 */
	public function renderHeadTitle()
	{
		$title = $this->getOption('head_title');
		if (is_callable($title))
		{
			$title = call_user_func($title, $this);
		}
		if (is_array($title))
		{
			$title = implode($this->getOption('title_separator'), $title);
		}
		return '<title>' . $this->escape($title) . '</title>';
	}

	/**
	 * Render all <link> tags.
	 *
	 * @return string
	 */
	public function renderHeadLink()
	{
		$items = $this->getOption('head_link', array());
		$index = $this->sort($items);
		$html = array();
		foreach ($index as $name)
		{
			$spec = $items[$name];
			if (is_callable($spec))
			{
				$spec = call_user_func($spec, $this);
			}
			if (is_null($spec))
			{
				continue;
			}
			if (is_string($spec))
			{
				$spec = array(
					'href' => $spec,
					'media' => 'all',
					'rel' => 'stylesheet',
				);
			}
			if (isset($spec['href']))
			{
				$spec['href'] = $this->render($spec['href']);
			}
			if (!isset($spec['rel']))
			{
				$spec['rel'] = 'stylesheet';
			}
			unset($spec['order']);
			$html[] = '<link ' . $this->createAttributesString($spec) . '>';
		}
		return implode("\n", $html);
	}

	/**
	 * Render all <style> tags.
	 *
	 * @return string
	 */
	public function renderHeadStyle()
	{
		$items = $this->getOption('head_style', array());
		$index = $this->sort($items);
		$html = array();
		foreach ($index as $name)
		{
			$spec = $items[$name];
			if (is_callable($spec))
			{
				$spec = call_user_func($spec, $this);
			}
			if (is_null($spec))
			{
				continue;
			}
			if (is_string($spec))
			{
				$spec = array(
					'content' => $spec,
					'attributes' => array(),
				);
			}
			$html[] = '<style ' . $this->createAttributesString($spec['attributes']) . '>' . $spec['content'] . '</style>';
		}
		return implode("\n", $html);
	}

	/**
	 * Render all <script> tags defined in the head_script config option.
	 *
	 * @return string
	 */
	public function renderHeadScript()
	{
		return $this->renderScript($this->getOption('head_script', array()));
	}

	/**
	 * Render all <script> tags defined in the inline_script config option.
	 *
	 * @return string
	 */
	public function renderInlineScript()
	{
		return $this->renderScript($this->getOption('inline_script', array()));
	}

	/**
	 * Render <script> tags from the given config options.
	 *
	 * @param array $config
	 *
	 * @return string
	 * @throws InvalidArgumentException if incorrect $mode is found
	 */
	public function renderScript(array $config)
	{
		$html = array();
		foreach ($config as $mode => $items)
		{
			if ($mode != 'file' && $mode != 'script')
			{
				throw new InvalidArgumentException(sprintf(
					"Expected \$mode to be 'file' or 'script', got '%s' instead"
					, $mode
				));
			}
			$index = $this->sort($items);
			foreach ($index as $name)
			{
				$spec = $items[$name];
				if (is_callable($spec))
				{
					$spec = call_user_func($spec, $this);
				}
				if (is_null($spec))
				{
					continue;
				}
				if ($mode == 'file')
				{
					if (is_string($spec))
					{
						$spec = array(
							'src' => $spec,
						);
					}
					if (isset($spec['src']))
					{
						$spec['src'] = $this->render($spec['src']);
					}
					unset($spec['order']);
					$html[] = '<script ' . $this->createAttributesString($spec) . '></script>';
				}
				elseif ($mode == 'script')
				{
					if (is_string($spec))
					{
						$spec = array(
							'content' => $spec,
							'attributes' => array(),
						);
					}
					$html[] = '<script ' . $this->createAttributesString($spec['attributes']) . '>' . $spec['content'] . '</script>';
				}
			}
		}
		return implode("\n", $html);
	}

	/**
	 * Render the opening <body> tag.
	 *
	 * @return string
	 */
	public function renderBodyStart()
	{
		$class = '';
		if ($this->getOption('responsive'))
		{
			if ($this->getOption('legacy_html_class_support'))
			{
				$class .= ' responsive';
			}
			$class .= ' wd-p-Responsive';

			if ($this->getOption('responsive_disable_large'))
			{
				$class .= ' wd-p-Responsive--disableLarge';
			}
		}
		if ($this->getOption('show_sidebar'))
		{
			if ($this->getOption('legacy_html_class_support'))
			{
				$class .= ' wd-show-sidebar';
			}
			$class .= ' wd-p-ShowSidebar';
		}
		return '<body class="' . $class . '">';
	}

	/**
	 * Render the start of the page <div>.
	 *
	 * @return string
	 */
	public function renderPageStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'pwrapper' : '';
		$legacyInnerClass = $this->getOption('legacy_html_class_support') ? 'pwrapper-wrapper' : '';

		return <<<HTML

<div class="wd-l-Page {$legacyClass}">
	<div class="wd-l-Page-inner {$legacyInnerClass}">
HTML;
	}

	/**
	 * Render the accessibility "skip" item.
	 *
	 * @return string
	 */
	public function renderSkipNavigation()
	{
		return '<div class="wd-Skip"><a accesskey="2" href="#skip-content">Skip Navigation</a></div>';
	}

	/**
	 * Render the template header which includes the dark top strip, red ribbon, and drop-down navigation.
	 *
	 * @return string
	 */
	public function renderHeader()
	{
		if ($this->getOption('show_header') !== true)
		{
			return '';
		}
		return implode("\n", array(
			$this->renderHeaderStart(),
			$this->renderTopStrip(),
			$this->renderRibbon(),
			$this->renderNavbar(),
			$this->renderHeaderEnd(),
		));
	}

	/**
	 * Render the start of the header <div>.
	 *
	 * @return string
	 */
	public function renderHeaderStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'hwrapper' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="header"' : '';

		return <<<HTML
<div class="wd-l-Header {$legacyClass}" {$legacyId}>
	<div class="wd-l-Header-inner">
HTML;
	}

	/**
	 * Render the dark top strip which contains sign-ons, alpha index, and directory info and map links.
	 *
	 * @return string
	 */
	public function renderTopStrip()
	{
		if ($this->getOption('show_top_strip') !== true)
		{
			return '';
		}
		return implode("\n", array(
			$this->renderTopStripStart(),
			$this->renderResponsiveToggles(),
			$this->renderTopStripMenuStart(),
			$this->renderSignons(),
			$this->renderDirectoryInfo(),
			$this->renderAlphaIndex(),
			$this->renderTopStripMenuEnd(),
			$this->renderTopStripEnd(),
		));
	}

	/**
	 * Render the start of the top-strip <div>.
	 *
	 * @return string
	 */
	public function renderTopStripStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-dark-ribbon' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="top-strip"' : '';

		return <<<HTML
<div class="wd-l-TopStrip {$legacyClass}" {$legacyId}>
	<div class="wd-l-TopStrip-inner">
HTML;
	}

	/**
	 * Render the responsive/off-canvas toggle buttons.
	 *
	 * @return string
	 */
	public function renderResponsiveToggles()
	{
		$html = array('<ul class="wd-ResponsiveToggles">');
		if ($this->getOption('show_sidebar') === true)
		{
			$html[] = '<li><button class="wd-ResponsiveToggles-nav">menu</button></li>';
		}
		if ($this->getOption('show_search_box') === true)
		{
			$html[] = '<li><button class="wd-ResponsiveToggles-search">search</button></li>';
		}
		$html[] = '</ul>';
		return implode("\n", $html);
	}

	/**
	 * Render the start of the top-strip menu <div>.
	 *
	 * @return string
	 */
	public function renderTopStripMenuStart()
	{
		return <<<HTML
<div class="wd-l-TopStripMenu">
HTML;
	}

	/**
	 * Render sign-on links for various organization wide systems.
	 *
	 * @return string
	 */
	public function renderSignons()
	{
		return <<<HTML
<ul class="wd-l-TopStripMenu-group wd-l-TopStripMenu-group--signons first">
	<li>
		<a class="wd-l-TopStripMenu-header" href="javascript:;">Sign-ons <i class="fa fa-angle-down"></i></a>
		<ul class="wd-l-TopStripMenu-toolbar">
			<li class="first"><a href="http://cymail.iastate.edu/">CyMail</a></li>
			<li><a href="http://outlook.iastate.edu/">Outlook</a></li>
			<li><a href="http://bb.its.iastate.edu/">Blackboard</a></li>
			<li class="last"><a href="http://accessplus.iastate.edu/">AccessPlus</a></li>
		</ul>
	</li>
</ul>
HTML;
	}

	/**
	 * Render links to an organizational wide alphabetical index.
	 *
	 * @return string
	 */
	public function renderAlphaIndex()
	{
		$index = array();
		foreach (range('A', 'Z') as $l)
		{
			$index[] = '<li class="' . ($l == 'A' ? 'first' : ($l == 'Z' ? 'last' : '')) . '"><a href="http://www.iastate.edu/index/' . $l . '/">' . $l . '</a></li>';
		}
		$index = implode('', $index);
		return <<<HTML
<ul class="wd-l-TopStripMenu-group wd-l-TopStripMenu-group--index">
	<li>
		<a class="wd-l-TopStripMenu-header" href="http://www.iastate.edu/index/A">Index</a>
		<ul class="wd-l-TopStripMenu-toolbar">
			{$index}
		</ul>
	</li>
</ul>
HTML;
	}

	/**
	 * Render links for directory, maps, and contact us.
	 *
	 * @return string
	 */
	public function renderDirectoryInfo()
	{
		return <<<HTML
<ul class="wd-l-TopStripMenu-group wd-l-TopStripMenu-group--directory last">
	<li>
		<a class="wd-l-TopStripMenu-header" href="javascript:;">More <i class="fa fa-angle-down"></i></a>
		<ul class="wd-l-TopStripMenu-toolbar">
			<li class="first"><a href="http://info.iastate.edu/">Directory</a></li>
			<li><a href="http://www.fpm.iastate.edu/maps/">Maps</a></li>
			<li class="last"><a href="http://www.iastate.edu/contact/">Contact Us</a></li>
		</ul>
	</li>
</ul>
HTML;
	}

	/**
	 * Render the closing tags for the top-strip menu.
	 *
	 * @return string
	 */
	public function renderTopStripMenuEnd()
	{
		return <<<HTML
</div>
HTML;
	}

	/**
	 * Render the closing tags for the top-strip.
	 *
	 * @return string
	 */
	public function renderTopStripEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the red ribbon which contains the search box, organization nameplate, site title and tagline, and
	 * any other post ribbon content.
	 *
	 * @return string
	 */
	public function renderRibbon()
	{
		if ($this->getOption('show_ribbon') !== true)
		{
			return '';
		}
		return implode("\n", array(
			$this->renderRibbonStart(),
			$this->renderSearchBox(),
			$this->renderNameplate(),
			$this->renderSiteTagline(),
			$this->renderSiteTitle(),
			$this->renderAfterRibbon(),
			$this->renderRibbonEnd(),
		));
	}

	/**
	 * Render the start of the ribbon <div>.
	 *
	 * @return string
	 */
	public function renderRibbonStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-red-ribbon' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="ribbon"' : '';
		return <<<HTML
<div class="wd-l-Ribbon {$legacyClass}" {$legacyId} role="banner">
	<div class="wd-l-Ribbon-inner">
HTML;
	}

	/**
	 * Render the search box with various config options.
	 *
	 * @return string
	 */
	public function renderSearchBox()
	{
		if ($this->getOption('show_search_box') !== true)
		{
			return '';
		}

		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-search-form' : '';

		$html = array();
		$html[] = '<form action="' . $this->getOption('search_action') . '" class="wd-l-SearchBox ' . $legacyClass . '" method="GET" role="search">';

		if (($value = $this->getOption('search_output')))
		{
			$html[] = '<input name="output" type="hidden" value="' . $this->escape($value) . '"/>';
		}
		if (($value = $this->getOption('search_client')))
		{
			$html[] = '<input name="client" type="hidden" value="' . $this->escape($value) . '"/>';
		}
		if (($value = $this->getOption('search_site')))
		{
			$html[] = '<input name="sitesearch" type="hidden" value="' . $this->escape($value) . '"/>';
		}
		if (($value = $this->getOption('search_style')))
		{
			$html[] = '<input name="proxystylesheet" type="hidden" value="' . $this->escape($value) . '"/>';
		}

		$html[] = '<input accesskey="s" name="q" title="Search" placeholder="' . $this->escape($this->getOption('search_placeholder')) . '" tabindex="1" type="text"/>';
		$html[] = '<input name="btnG" title="Submit" type="submit" value="' . $this->escape($this->getOption('search_submit')) . '"/>';
		$html[] = '</form>';

		return implode("\n", $html);
	}

	/**
	 * Render the nameplate.
	 *
	 * @return string
	 */
	public function renderNameplate()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'nameplate' : '';
		return <<<HTML
<a accesskey="1" class="wd-Nameplate {$legacyClass}" href="{$this->getOption('org_url')}">{$this->renderSprite()}</a>
HTML;
	}

	/**
	 * Render the sprite with the theme_asset_path prepended and using the org_title as the alt attribute.
	 *
	 * @return string
	 */
	public function renderSprite()
	{
		return '<img alt="' . $this->getOption('org_title') . '" src="' . $this->render($this->getOption('sprite')) . '"/>';
	}

	/**
	 * Render the site_title with an optional site_url.
	 *
	 * @return string
	 */
	public function renderSiteTitle()
	{
		if ($this->getOption('show_site_title') !== true)
		{
			return '';
		}
		$html = $this->escape($this->getOption('site_title'));
		if ($this->getOption('site_url') !== null)
		{
			$html = '<a href="' . $this->escape($this->render($this->getOption('site_url')) ? : '/') . '">' . $html . '</a>';
		}
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'site-title sub-title' : '';
		return '<div class="wd-l-MastTitle-siteTitle ' . $legacyClass . '">' . $html . '</div>';
	}

	/**
	 * Render the site_tagline with an optional site_tagline_url.
	 *
	 * @return string
	 */
	public function renderSiteTagline()
	{
		if ($this->getOption('show_site_tagline') !== true)
		{
			return '';
		}
		$html = $this->escape($this->getOption('site_tagline'));
		if ($this->getOption('site_tagline_url'))
		{
			$html = '<a href="' . $this->escape($this->getOption('site_tagline_url')) . '">' . $html . '</a>';
		}
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'site-tagline sub-title' : '';
		return '<div class="wd-l-MastTitle-siteTagline ' . $legacyClass . '">' . $html . '</div>';
	}

	/**
	 * Render the after_ribbon config option.
	 *
	 * @return mixed
	 */
	public function renderAfterRibbon()
	{
		return $this->getOption('after_ribbon');
	}

	public function renderRibbonEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the navbar which is the drop-down navigation bar.
	 *
	 * @return string
	 */
	public function renderNavbar()
	{
		if ($this->getOption('show_navbar') !== true)
		{
			return '';
		}
		return <<<HTML
<div class="wd-l-Navbar">
	<div class="wd-l-Navbar-inner">
		<div role="navigation">
			{$this->renderNavigation($this->getOption('navbar'))}
		</div>
		{$this->renderNavigationAlternative($this->getOption('navbar'))}
	</div>
</div>
HTML;
	}

	/**
	 * Render the closing tags for the header.
	 *
	 * @return string
	 */
	public function renderHeaderEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render a simple drop-down alternative to navigations for smaller screens.
	 *
	 * @param array $pages
	 *
	 * @return string
	 */
	public function renderNavigationAlternative($pages)
	{
		$activePages = array();
		foreach ($pages as $i => $page)
		{
			$activePages[$i] = $this->calculateActiveRating($page);
		}
		$this->activateMaxRating($activePages);

		$legacyClass = $this->getOption('legacy_html_class_support') ? 'navigation-alternative' : '';
		$html = array();
		$html[] = '<select class="wd-Navigation-alt ' . $legacyClass . '" onchange="window.location=this.value" title="Navigation">';

		$hasActiveOption = false;
		$index = $this->sort($pages);
		foreach ($index as $i)
		{
			$page = $pages[$i];
			$isActive = $activePages[$i]['value'] > 0;

			$attr = array(
				'value' => $this->render($page['uri']),
			);
			if ($isActive && !$hasActiveOption)
			{
				$attr['selected'] = 'selected';
				$hasActiveOption = true;
			}
			$html[] = '<option '. $this->createAttributesString($attr) .'>' . $this->escape($page['label']) . '</option>';
			if (is_array($page['pages']))
			{
				$childIndex = $this->sort($page['pages']);
				foreach ($childIndex as $ci)
				{
					$child = $page['pages'][$ci];
					$isActive = $activePages[$i]['pages'][$ci]['value'] > 0;

					$attr = array(
						'value' => $this->render($child['uri']),
					);
					if ($isActive && !$hasActiveOption)
					{
						$attr['selected'] = 'selected';
						$hasActiveOption = true;
					}
					$html[] = '<option '. $this->createAttributesString($attr) .'>- ' . $this->escape($child['label']) . '</option>';
				}
			}
		}
		$html[] = '</select>';
		return implode("\n", $html);
	}

	/**
	 * Check how much a navigation page matches the requested url.
	 *
	 * Returns an array containing the percentage match for each page. The match for a node is
	 * the maximum of the node itself and all of its descendant nodes' matches.
	 *
	 * @param array $page
	 *
	 * @return array
	 */
	protected function calculateActiveRating($page)
	{
		$active = array(
			'value' => 0,
			'pages' => array(),
		);
		if (!isset($page['noselect']) || $page['noselect'] !== true)
		{
			$reqUri = $this->getOption('request_uri', $_SERVER['REQUEST_URI']);
			if (isset($page['uri']))
			{
				$uri = $this->render($page['uri']);
			}
			elseif (isset($page['route']))
			{
				$uri = $this->url($page['route']);
			}
			else
			{
				$uri = '';
			}
			$value = 0;
			if ($uri == $reqUri)
			{
				$value = 100;
			}
			elseif ($uri != '' && strpos($reqUri, $uri) === 0)
			{
				$value = round(100 * strlen($uri) / strlen($reqUri));
			}
			if (isset($page['pattern']))
			{
				if (preg_match($page['pattern'], $reqUri))
				{
					$value = 100;
				}
			}
			if ($value > 0 && isset($page['nopattern']))
			{
				if (preg_match($page['nopattern'], $reqUri))
				{
					$value = 0;
				}
			}
			if (!empty($page['pages']))
			{
				foreach ($page['pages'] as $i => $child)
				{
					$active['pages'][$i] = $this->calculateActiveRating($child);
				}
				$value = max($value, max(array_map(function ($child)
				{
					return $child['value'];
				}, $active['pages'])));
			}
			$active['value'] = $value;
		}
		return $active;
	}

	/**
	 * De-activate all except for the most 'active' pages within the tree.
	 *
	 * @param array $pages
	 */
	protected function activateMaxRating(&$pages)
	{
		if (empty($pages))
		{
			return;
		}
		$max = max(array_map(function ($page)
		{
			return $page['value'];
		}, $pages));
		foreach ($pages as &$page)
		{
			if ($page['value'] < $max)
			{
				$page['value'] = 0;
			}
			$this->activateMaxRating($page['pages']);
		}
	}

	/**
	 * Returns the keys of the given array sorted by the order property of each
	 * array item in ascending order.
	 *
	 * <code>
	 * print $this->sort(array(
	 *     'item1' => array(
	 *         'label' => 'Item 1',
	 *     ),
	 *     'item2' => array(
	 *         'label' => 'Item 2',
	 *         'order' => -1,
	 *     ),
	 * ));
	 * // array(
	 * //     'item2',
	 * //     'item1',
	 * // )
	 * </code>
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function sort($items)
	{
		$index = array();
		$c = 0;
		foreach ($items as $i => $item)
		{
			if (is_array($item) && isset($item['order']))
			{
				$index[$i] = $item['order'];
			}
			else
			{
				$index[$i] = $c++;
			}
		}
		asort($index);
		return array_keys($index);
	}

	/**
	 * Renders a navigation container into an unordered html list.
	 *
	 * <code>
	 * echo $theme->renderNavigation(array(
	 *     array(
	 *         'label' => 'Heading',
	 *     ),
	 *     array(
	 *         'label' => 'Link 1',
	 *         'uri' => '/some/page',
	 *     ),
	 * ));
	 * // <ul class="wd-Navigation">
	 * //   <li class="wd-Navigation-node"><h2 class="wd-Navigation-heading">Heading</h2></li>
	 * //   <li class="wd-Navigation-node"><a class="wd-Navigation-link" href="/some/page">Link 1</a></li>
	 * // </ul>
	 * </code>
	 *
	 * @param array $pages Array of navigation pages to render. The following keys are supported:
	 *     <ul>
	 *         <li>label (string): page label (required)</li>
	 *         <li>escape (boolean): will not escape label if set to false (default: true)</li>
	 *         <li>translate (boolean): will not translate label if set to false (default: true)</li>
	 *         <li>uri (string): page link. will display a heading item if this key (or route) is not provided.</li>
	 *         <li>route (string|array): complex page route. will display a heading item if this key (or uri) is not provided.</li>
	 *         <li>attributes (array): an array of html element attributes to be applied to the link.</li>
	 *         <li>order (integer): the order in which the item shows up in the list (lower number means higher in the list)</li>
	 *         <li>pages (array): array of sub navigation items</li>
	 *         <li>show_children (boolean): whether to display sub-pages when parent is not active</li>
	 *         <li>pattern (regexp): will mark item as selected if requested url matches this pattern</li>
	 *         <li>nopattern (regexp): will mark item as not selected if requested url matches this pattern</li>
	 *         <li>noselect (boolean): will mark item as not selected item regardless of requested url</li>
	 *         <li>allowed_roles (string[]): item is shown only if authorization_callback returns true for any of the given roles</li>
	 *         <li>allowed_permissions (string[]): item is shown only if authorization_callback returns true for any of the given permissions</li>
	 *         <li>denied_roles (string[]): item is not shown if authorization_callback returns true for any of the given roles</li>
	 *         <li>denied_permissions (string[]): item is not shown if authorization_callback returns true for any of the given permissions</li>
	 *     </ul>
	 * @param int $depth (internal)
	 * @param array $activePages (internal)
	 *
	 * @return string
	 */
	public function renderNavigation($pages, $depth = 0, $activePages = array())
	{
		if (empty($pages))
		{
			return '';
		}

		$index = $this->sort($pages);

		// if starting from a root node, calculate likeliness of how 'active' each page is
		if ($depth == 0)
		{
			$activePages = array();
			foreach ($pages as $i => $page)
			{
				$activePages[$i] = $this->calculateActiveRating($page);
			}
			$this->activateMaxRating($activePages);
		}

		$html = array();
		foreach ($index as $i)
		{
			$page = $pages[$i];
			$active = $activePages[$i]['value'] > 0;

			// BC support for 'showchildren'
			if (isset($page['showchildren']) && !isset($page['show_children']))
			{
				$page['show_children'] = $page['showchildren'];
			}
			$showChildren = isset($page['show_children']) && $page['show_children'];

			// BC support for 'roles' and 'permissions'
			if (isset($page['roles']) && !isset($page['allowed_roles']))
			{
				$page['allowed_roles'] = $page['roles'];
			}
			if (isset($page['permissions']) && !isset($page['allowed_permissions']))
			{
				$page['allowed_permissions'] = $page['permissions'];
			}

			// perform authorization check to see if page visible
			if (isset($page['allowed_roles']) || isset($page['allowed_permissions']))
			{
				$allowedByRole = isset($page['allowed_roles']) && $this->isAllowed($page['allowed_roles'], 'role');
				$allowedByPermission = isset($page['allowed_permissions']) && $this->isAllowed($page['allowed_permissions'], 'permission');
				if (!$allowedByRole && !$allowedByPermission)
				{
					continue;
				}
			}
			if (isset($page['denied_roles']) || isset($page['denied_permissions']))
			{
				$deniedByRole = isset($page['denied_roles']) && $this->isAllowed($page['denied_roles'], 'role');
				$deniedByPermission = isset($page['denied_permissions']) && $this->isAllowed($page['denied_permissions'], 'permission');
				if ($deniedByRole || $deniedByPermission)
				{
					continue;
				}
			}

			$subNav = null;
			if (isset($page['pages']) && is_array($page['pages']) && ($showChildren || $active))
			{
				$subNav = $this->renderNavigation($page['pages'], $depth + 1, $activePages[$i]['pages']);
			}

			$nodeClass = array('wd-Navigation-node');
			if (!empty($subNav))
			{
				$nodeClass[] = 'is-' . ($showChildren || $active ? 'expanded' : 'collapsed');
			}

			$html[] = '<li class="' . implode(' ', $nodeClass) . '">';
			$attr = array();
			foreach ($page as $key => $value)
			{
				$skip = array(
					'label',
					'escape',
					'translate',
					'uri',
					'route',
					'order',
					'pages',
					'show_children', 'showchildren',
					'pattern',
					'nopattern',
					'noselect',
					'roles', 'allowed_roles', 'denied_roles',
					'permissions', 'allowed_permissions', 'denied_permissions',
				);
				if (!in_array($key, $skip))
				{
					$attr[$key] = $value;
				}
			}
			if (isset($page['attributes']))
			{
				foreach ($page['attributes'] as $key => $value)
				{
					$attr[$key] = $value;
				}
			}
			if (isset($attr['class']))
			{
				$attr['class'] = explode(' ', $attr['class']);
			}
			else
			{
				$attr['class'] = array();
			}
			if (isset($page['uri']) || isset($page['route']))
			{
				if (isset($page['uri']))
				{
					$attr['href'] = $this->render($page['uri']);
				}
				elseif (isset($page['route']))
				{
					$attr['href'] = $this->url($page['route']);
				}
				$attr['class'][] = 'wd-Navigation-link';
				if ($active)
				{
					$leaf = true;
					foreach ($activePages[$i]['pages'] as $childPage)
					{
						if ($childPage['value'] > 0)
						{
							$leaf = false;
							break;
						}
					}
					$attr['class'][] = $leaf ? 'is-active' : 'is-activeTrail';
				}
				$tag = 'a';
			}
			else
			{
				$attr['class'][] = 'wd-Navigation-heading';
				$tag = 'h2';
			}
			$label = isset($page['label']) ? $page['label'] : '';
			if (!isset($page['escape']) || $page['escape'] !== false)
			{
				$label = $this->escape($label);
			}
			if (!isset($page['translate']) || $page['translate'] !== false)
			{
				$label = $this->translate($label);
			}
			$attr['class'] = implode(' ', $attr['class']);
			$html[] = '<' . $tag . ' ' . $this->createAttributesString($attr) . '>' . $label . '</' . $tag . '>';

			if (!empty($subNav))
			{
				$html[] = $subNav;
			}

			$html[] = '</li>';
		}

		if (count($html) == 0)
		{
			return '';
		}

		$navClass = array();
		$navClass[] = $depth == 0 ? 'wd-Navigation' : 'wd-Navigation-subnav';
		array_unshift($html, '<ul class="' . implode(' ', $navClass) . '">');

		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Render the start of the container <div>.
	 *
	 * @return string
	 */
	public function renderFrontStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'cwrapper' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="container"' : '';

		return <<<HTML
<div class="wd-l-Container" id="skip-content">
<div class="wd-l-Container-inner wd-l-Container-inner-FrontPage">
<div class="wd-l-Content" id="content" role="main">
<div class="wd-l-Content-inner wd-l-Content-inner-FrontPage">
	<div class="wd-l-Container-inner {$legacyClass}" {$legacyId}>
HTML;
	}

	/**
	 * Render the start of the container <div>.
	 *
	 * @return string
	 */
	public function renderContainerStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'cwrapper' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="container"' : '';

		return <<<HTML
<div class="wd-l-Container" id="skip-content">
	<div class="wd-l-Container-inner {$legacyClass}" {$legacyId}>
HTML;
	}

	/**
	 * Render the sidebar navigation items and any other post sidebar content.
	 *
	 * @return string
	 */
	public function renderSidebar()
	{
		if ($this->getOption('show_sidebar') !== true)
		{
			return '';
		}
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-sidebar' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="sidebar"' : '';
		return <<<HTML
<div class="wd-l-Sidebar {$legacyClass}" {$legacyId}>
	<div class="wd-l-Sidebar-inner">
		<ul class="wd-ResponsiveToggles">
			<li><button class="wd-ResponsiveToggles-nav">menu</button></li>
		</ul>
		<div role="navigation">
			{$this->renderNavigation($this->getOption('sidebar'))}
		</div>
		{$this->renderAfterSidebar()}
	</div>
</div>
HTML;
	}

	/**
	 * Render the after_sidebar config option.
	 *
	 * @return mixed
	 */
	public function renderAfterSidebar()
	{
		return $this->getOption('after_sidebar');
	}

	/**
	 * Render the start of the content <div>.
	 *
	 * @return string
	 */
	public function renderContentStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-content' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="content"' : '';

		return <<<HTML
<div class="wd-l-Content {$legacyClass}" {$legacyId} role="main">
	<div class="wd-l-Content-inner">
HTML;
	}

	/**
	 * Render the anchor_image.
	 *
	 * @return mixed
	 */
	public function renderAnchorImage()
	{
		return $this->getOption('anchor_image');
	}

	/**
	 * Render the page_title with an <h1> tag.
	 *
	 * @return string
	 */
	public function renderPageTitle()
	{
		if ($this->getOption('show_page_title') !== true)
		{
			return '';
		}
		return '<h1><a href="/" style="text-decoration: none"><span style="color:red">ISU <em>FLU</em></span><span style="color:gold">ture</span></a></h1>';
		//return '<h1>' . $this->escape($this->getOption('page_title')) . '</h1>';
	}

	/**
	 * Render the closing tags for the content.
	 *
	 * @return string
	 */
	public function renderContentEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the closing tags for the container.
	 *
	 * @return string
	 */
	public function renderContainerEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the footer which includes the nameplate, page_footer, footer_copyright, and post
	 * footer content.
	 *
	 * @return string
	 */
	public function renderFooter()
	{
		if ($this->getOption('show_footer') !== true)
		{
			return '';
		}
		return implode("\n", array(
			//$this->renderFooterStart(),
			//$this->renderBeforeFooter(),
			//$this->renderPageFooterStart(),
			//$this->renderPageFooterNameplate(),
			$this->renderPageFooterContent(),
			//$this->renderPageFooterEnd(),
			//$this->renderAfterFooter(),
			//$this->renderFooterEnd(),
		));
	}

	/**
	 * Render the start of the footer <div>.
	 *
	 * @return string
	 */
	public function renderFooterStart()
	{
		$legacyFooterClass = $this->getOption('legacy_html_class_support') ? 'fwrapper' : '';
		return <<<HTML
<div class="wd-l-Footer">
	<div class="wd-l-Footer-inner {$legacyFooterClass}">
HTML;
	}

	/**
	 * Render the before_footer config option.
	 *
	 * @return mixed
	 */
	public function renderBeforeFooter()
	{
		return $this->getOption('before_footer');
	}

	/**
	 * Render the start of the page-footer <div>.
	 *
	 * @return string
	 */
	public function renderPageFooterStart()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'isu-footer' : '';
		$legacyId = $this->getOption('legacy_html_id_support') ? 'id="footer"' : '';
		return <<<HTML
<div class="wd-l-PageFooter {$legacyClass}" {$legacyId} role="contentinfo">
	<div class="wd-l-PageFooter-inner">
		<div class="wd-Grid wd-Grid--fitToFill wd-Grid--noGutter">
HTML;
	}

	/**
	 * Render the page-footer nameplate.
	 *
	 * @return string
	 */
	public function renderPageFooterNameplate()
	{
		$legacyClass = $this->getOption('legacy_html_class_support') ? 'nameplate' : '';
		return <<<HTML
<div class="wd-Grid-cell wd-Grid-cell--1">
	<div class="wd-l-PageFooter-nameplate">
		<a class="wd-Nameplate {$legacyClass}" href="{$this->getOption('org_url')}">{$this->renderSprite()}</a>
	</div>
</div>
HTML;
	}

	/**
	 * Render the page-footer content.
	 *
	 * @return string
	 */
	public function renderPageFooterContent()
	{
		return <<<HTML
<div>
	<div class="wd-l-PageFooter-content">
		{$this->renderPageFooter()}
	</div>
</div>
HTML;
	}

	/**
	 * Render the closing tags for the page-footer.
	 *
	 * @return string
	 */
	public function renderPageFooterEnd()
	{
		return <<<HTML
		</div>
	</div>
</div>
HTML;
	}

	/**
	 * Render the closing tags for the footer.
	 *
	 * @return string
	 */
	public function renderFooterEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the page_footer injecting email links if a placeholder for one exists.
	 *
	 * Example:
	 *   - '<p>Contact {{> email|sample@iastate.edu}}.</p>'
	 *   - '<p>Contact {{> email|sample@iastate.edu|Sample label}}.</p>'
	 *
	 * @return string
	 */
	public function renderPageFooter()
	{
		return $this->render($this->getOption('page_footer'));
	}

	/**
	 * Render the footer_copyright injecting the current year if a placeholder for it exists.
	 *
	 * Example: '<p>Copyright {{year}}.</p>'
	 *
	 * @return string
	 */
	public function renderFooterCopyright()
	{
		return $this->render($this->getOption('footer_copyright'));
	}

	/**
	 * Render the after_footer config option.
	 *
	 * @return mixed
	 */
	public function renderAfterFooter()
	{
		return $this->getOption('after_footer');
	}

	/**
	 * Render the loading bar.
	 *
	 * @return string
	 */
	public function renderLoadingBar()
	{
		return '<div id="loading" style="display:none">Loading...</div>';
	}

	/**
	 * Render the closing tags for the page.
	 *
	 * @return string
	 */
	public function renderPageEnd()
	{
		return <<<HTML
	</div>
</div>
HTML;
	}

	/**
	 * Render the closing <body> tag.
	 *
	 * @return string
	 */
	public function renderBodyEnd()
	{
		return '</body>';
	}

	/**
	 * Render closing <html> tag.
	 *
	 * @return string
	 */
	public function renderHtmlEnd()
	{
		return '</html>';
	}

	/**
	 * Returns a spamproof mailto link for the provided email address with an optional label.
	 *
	 * @param string $email email address or Net-ID
	 * @param string $label label for the mailto link (defaults to the email address)
	 *
	 * @throws InvalidArgumentException if empty email address given
	 * @return string
	 */
	public function email($email, $label = null)
	{
		if ($email == '')
		{
			throw new InvalidArgumentException('Email cannot be empty');
		}
		if (strpos($email, '@') === false)
		{
			$email .= '@iastate.edu';
		}
		$json = array_map('json_encode', explode('@', $email));
		$text = str_replace(array('@', '.'), array(' (at) ', ' (dot) '), $email);
		$noscript = $label ? "$label ($text)" : $text;
		$email = "[{$json[0]}, {$json[1]}].join('@')";
		$label = $label ? json_encode($label) : $email;

		return <<<HTML
<script>document.write('<a href="mailto:'+ {$email} +'">'+ {$label} +'</a>')</script><noscript>{$noscript}</noscript>
HTML;
	}

	/**
	 * Render a string replacing placeholders with variables.
	 *
	 * Place {{base_path}} or {{site_url}} in the string to replace with the respective
	 * configuration option.
	 *
	 * <code>
	 * echo $this->render('{{base_path}}/foo-bar');
	 * </code>
	 *
	 * @param string $template
	 * @param array $vars custom variables
	 *
	 * @return string
	 * @throws RuntimeException if a filter does not have a valid callback
	 */
	public function render($template, $vars = array())
	{
		date_default_timezone_set('America/Chicago');
		$vars = array_merge(array(
			'{{asset_path}}' => $this->getOption('asset_path'),
			'{{base_path}}' => $this->getOption('base_path'),
			'{{site_url}}' => $this->getOption('site_url'),
			'{{module_asset_path}}' => $this->getOption('module_asset_path'),
			'{{theme_asset_path}}' => $this->getOption('theme_asset_path'),
			'{{year}}' => date('Y'),
		), $vars);

		foreach ($this->getOption('render_tags', array()) as $name => $callback)
		{
			if (!is_callable($callback))
			{
				throw new RuntimeException("'render_tag.$name' option must be a valid callback");
			}
			$vars = array_replace($vars, $this->renderTag($template, '{{>' . $name, $callback));
			$vars = array_replace($vars, $this->renderTag($template, '{{> ' . $name, $callback));
		}

		$rendered = strtr($template, $vars);
		if (strpos($rendered, '{{') !== false && $rendered !== $template)
		{
			return $this->render($rendered, $vars);
		}
		return $rendered;
	}

	/**
	 * Render a particular tag and return an array containing the tag => string transformation.
	 *
	 * @param string $template The template to render
	 * @param string $delimiter The tag opening delimiter
	 * @param callable $callback The tag callback
	 *
	 * @return array
	 */
	protected function renderTag($template, $delimiter, $callback)
	{
		$vars = array();
		if (strpos($template, $delimiter) !== false)
		{
			$offset = 0;
			while (($pos = strpos($template, $delimiter, $offset)) !== false)
			{
				if (!in_array(substr($template, $pos + strlen($delimiter), 1), array('|', '}'), true))
				{
					$offset++;
					continue;
				}
				$length = strpos($template, '}}', $pos) + 2 - $pos;
				$match = substr($template, $pos, $length);
				$vars[$match] = call_user_func($callback, substr($match, strlen($delimiter), -2), $this);
				$offset = $pos + $length;
			}
		}
		return $vars;
	}

	/**
	 * @deprecated since 1.2.1
	 * @see Theme::render()
	 */
	public function renderVariables()
	{
		return call_user_func_array(array($this, 'render'), func_get_args());
	}

	/**
	 * Create an html element attributes string from an array of attributes.
	 *
	 * <code>
	 * echo '<meta '. $theme->createAttributesString(array('charset' => 'utf-8')) .'/>;
	 * // '<meta charset="utf-8"/>'
	 * </code>
	 *
	 * @param array $attr
	 *
	 * @return string
	 * @throws InvalidArgumentException for non-scalar attribute values
	 */
	public function createAttributesString(array $attr = array())
	{
		$html = array();
		foreach ($attr as $name => $value)
		{
			if (!is_scalar($value))
			{
				throw new InvalidArgumentException(sprintf(
					"HTML attribute value for '%s' must be a scalar, got '%s' instead"
					, $name
					, gettype($value)
				));
			}

			$html[] = $this->escape($name) . '="' . $this->escape($value) . '"';
		}
		return implode(' ', $html);
	}

	/**
	 * Escape an html snippet including single quotes and with utf-8 encoding.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function escape($html)
	{
		return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Translate a string using the translator_callback option.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function translate($text)
	{
		$callback = $this->getOption('translator_callback');
		if (is_callable($callback))
		{
			return call_user_func($callback, $text);
		}
		return $text;
	}

	/**
	 * Generate a URL using the route_callback option.
	 *
	 * @param array|string $args
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	public function url($args)
	{
		$callback = $this->getOption('route_callback');
		if (!is_callable($callback))
		{
			throw new RuntimeException("'route_callback' option must be a valid callback");
		}
		return call_user_func($callback, $args);
	}

	/**
	 * Check whether allowed access based on a set of roles using the authorization_callback option.
	 *
	 * @param array|string $params
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isAllowed($params, $name = null)
	{
		$callback = $this->getOption('authorization_callback');
		$allowed = true;
		if ($callback !== null)
		{
			$allowed = call_user_func($callback, $params, $name);
		}
		return $allowed;
	}
}
