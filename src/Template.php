<?php
/**
 * Email package that provides a easy email templateing service.
 * The email is constructed and sent of through the configured
 * transport protocol.
 *
 * Toemaar (http://code.grasvezel.nl/)
 *
 * @link      http://code.grasvezel.nl/
 * @package   Toemaar/Service/Email
 * @copyright Copyright (c) 2005-2015 Toemaar. (http://www.toemaar.nl)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Toemaar\Email;

class Template
{

    /**
     * @var string
     */
    protected $_i18n = null;

    /**
     * @var string
     */
    protected $_template = 'default';

    /**
     * @var null|string
     */
    protected $_htmlTemplate = null;

    /**
     * @var null|string
     */
    protected $_textTemplate = null;

    /**
     * @var string[]
     */
    protected $_variables = array();

    /**
     * @var string
     */
    protected $_templatesRoot = __DIR__ . '/templates';

    /**
     * @var null|Testament
     */
    protected $_testament = null;

    /**
     * @var string
     */
    protected $_renderedHtml = '';

    /**
     * @var string
     */
    protected $_renderedText = '';
    
    /**
     * 
     * @var Template
     */
    protected $_parent = null;
    
    /**
     *
     * @var string
     */
    protected $_parentTemplate = '';
    
    public function __construct($template = null, $i18n = null){
        
        if(null !== $template)
            $this->setTemplate($template);
        
        if(null !== $i18n)
            $this->setI18n($i18n);
        
    }

    /**
     * Set email i18n
     *
     * @param string $i18n
     */
    public function setI18n($i18n){
        $this->_i18n = $i18n;
    }

    /**
     * Set template name
     *
     * @param string $template
     *
     * @return Template
     * @throws Exception
     */
    public function setTemplate($template){
        if(!is_dir($this->_templatesRoot . $template))
            throw new Exception('Email Template ' . $template . ' could not be found in (' . $this->_templatesRoot . ')');

        $this->_template = $template;
        return $this;
    }
    
    /**
     * Property overloading: get variable value
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->_variables[$name])) {
            return null;
        }
    
        return $this->_variables[$name];
    }
    
    /**
     * Get a single view variable
     *
     * @param  string       $name
     * @param  mixed|null   $default (optional) default value if the variable is not present.
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_variables)) {
            return $this->_variables[$name];
        }
    
        return $default;
    }

    /**
     * Set view variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return Template
     */
    public function setVariable($name, $value)
    {
        $this->_variables[(string) $name] = $value;
        
        if(null !== $this->_parent)
            $this->_parent->setVariables($this->_variables);
        return $this;
    }

    /**
     * Set view variables en masse
     *
     *
     * @param  array $variables
     * @return Template
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;
        if(null !== $this->_parent)
            $this->_parent->setVariables($this->_variables);
        
        return $this;
    }

    protected function findFiles(){
        $path = $this->_templatesRoot .$this->_template;
        if(!is_dir($path))
            throw new Exception('Template ' . $this->_template . ' is not found.');

        if(!is_file($path . '/' . $this->_i18n . '.json'))
            throw new Exception('Template testament file is not found. (' . $this->_template . ')');
        
        $this->_templatesRoot = $path;
        $testamentObject = json_decode(file_get_contents($this->_templatesRoot . '/' . $this->_i18n . '.json'));

        if(null == $testamentObject)
            throw new Exception('Invalid Json in testament');

        $this->_testament = new Testament();
        $this->_testament
            ->setFromAddress($testamentObject->Replyto)
            ->setFromName($testamentObject->From->Name)
            ->setSubject($testamentObject->Subject)
            ->setReplyTo($testamentObject->From->Address);

        
        if(!is_file($this->_templatesRoot . '/' . $this->_i18n . '.phtml'))
            throw new Exception('Html file for template could not be found. (' . $this->_template . '/' . $this->_i18n . '.phtml)');
        
        if(!is_file($this->_templatesRoot . '/' . $this->_i18n . '.ptext'))
            throw new Exception('Text file for template could not be found. (' . $this->_template . '/' . $this->_i18n . '.ptext)');
                    
        $this->_htmlTemplate = $this->_templatesRoot . '/' . $this->_i18n . '.phtml';            
        $this->_textTemplate = $this->_templatesRoot . '/' . $this->_i18n . '.ptext';
    }

    /**
     * @return Testament
     */
    public function getTestament(){
        return $this->_testament;
    }

    /**
     * @param $templateFile
     *
     * @return string
     */
    protected function readTemplateFile($templateFile){
        ob_start();
        include($templateFile);
        return ob_get_clean();
        
    }

    /**
     * Get html content of email
     * @return string
     */
    public function getHtml()
    {
        return $this->_renderedHtml;
    }


    /**
     * Get Text content of email
     *
     * @return string
     */
    public function getText()
    {
        return $this->_renderedText;
    }

    /**
     * Render html and text content for email
     *
     * @return $this
     * @throws Exception
     */
    public function render()
    {
        $this->findFiles();
        $this->_renderedHtml = $this->readTemplateFile($this->_htmlTemplate); 
        $this->_renderedText = $this->readTemplateFile($this->_textTemplate); 
        
        return $this;
    }
    
}