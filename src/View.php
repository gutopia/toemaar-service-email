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


class View
{
    /**
     * Text view constants
     */
    const VIEW_TEXT = 0;

    /**
     * Text view constants
     */
    const VIEW_HTML = 1;
    
    /**
     * Type of view
     * @var string
     */
    protected $_type = self::VIEW_HTML;
    
    /**
     * View variables
     * @var array
     */
    protected $variables = array();

    /**
     * Set content type for view
     * @param $type
     *
     * @return EmailView
     * @throws Exception
     */
    public function setType($type){
        if($type != self::VIEW_HTML && $type != self::VIEW_TEXT)
            throw new Exception('Unknown type specified');
        
        $this->_type = $type;

        return $this;
    }
    
    /**
     * Set view variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return EmailView
     */
    public function setVariable($name, $value)
    {
        $this->variables[(string) $name] = $value;
        return $this;
    }    
    
    /**
     * Get view variables
     *
     * @return array|\ArrayAccess|\Traversable
     */
    public function getVariables()
    {
        return $this->variables;
    }
    
    /**
     * Clear all variables
     *
     * Resets the internal variable container to an empty container.
     *
     * @return EmailView
     */
    public function clearVariables()
    {
        $this->variables = array();

        return $this;
    }    
    
    /**
     * Set a set of variables at once.
     * 
     * @param array $variables
     * @return EmailView
     */
    public function setVariables(array $variables) 
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }
}