<?php
/**
 * PHPSpec
 *
 * LICENSE
 *
 * This file is subject to the GNU Lesser General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/lgpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpspec.net so we can send you a copy immediately.
 *
 * @category  PHPSpec
 * @package   PHPSpec
 * @copyright Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                    Marcello Duarte
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
namespace PHPSpec\Specification;

use PHPSpec\Specification\Interceptor\InterceptorFactory;
use PHPSpec\Specification\Result\Pending;
use PHPSpec\Specification\Result\DeliberateFailure;
use PHPSpec\Specification\Exception as SpecificationException;

use PHPSpec\Matcher\MatcherFactory;

use PHPSpec\Runner\Reporter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ExampleGroup
{ 
    /**
     * The Matcher Factory
     *
     * @var PHPSpec\Matcher\MatcherFactory
     */
    private $_matcherFactory;
    
    /**
     * Shared example a example group can behave like
     *
     * @var string
     */
    public $itBehavesLike;
    
    /**
     * Shared Examples
     *
     * @var array[string name of the shared example]<Closure>
     */
    protected $sharedExamples = array();
    
    /**
     * Override for having it called once before all examples are ran in one
     * group
     */
    public function beforeAll()
    {
        
    }
    
    /**
     * Override for having it called before every example is called in a group
     */
    public function before()
    {
        
    }
    
    /**
     * Override for having it called once after all examples are ran in one
     * group
     */
    public function afterAll()
    {
        
    }
    
    /**
     * Override for having it called after every example is called in a group
     */
    public function after()
    {
        
    }
    
    /**
     * Encapsulate result with a interceptor to be able to add expectations
     * to the values
     * 
     * @return \PHPSpec\Specification\Interceptor
     */
    public function spec()
    {
        $interceptor = call_user_func_array(
            array(
                '\PHPSpec\Specification\Interceptor\InterceptorFactory',
                'create'),
            func_get_args()
        );
        $interceptor->setMatcherFactory($this->getMatcherFactory());
        return $interceptor;
    }
    
    /**
     * Marks example as pending
     * 
     * @param string $message
     * @throws \PHPSpec\Specification\Result\Pending
     */
    public function pending($message = 'No reason given')
    {
        throw new Pending($message);
    }
    
    /**
     * Marks example as failure
     * 
     * @param string $message
     * @throws \PHPSpec\Specification\Result\DeliberateFailure
     */
    public function fail($message = '')
    {
        $message = empty($message) ? '' : PHP_EOL . '       ' . $message;
        throw new DeliberateFailure(
            'RuntimeError:' . $message
        );
    }
    
    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @param array  $arguments
     * @return object
     */
    public function double($class = 'stdClass')
    {
        if (class_exists('Mockery')) {
            $double = \Mockery::mock($class);
            return $double;
        }
        throw new \PHPSpec\Exception('Mockery is not installed');
    }

    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @param array  $arguments
     * @return object
     */
    public function mock($class = 'stdClass')
    {
        return $this->double($class);
    }

    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @param array  $arguments
     * @return object
     */
    public function stub($class = 'stdClass')
    {
        return $this->double($class, $stubs, $arguments);
    }
    
    /**
     * Sets the matcher factory
     *
     * @param PHPSpec\Matcher\MatcherFactory
     */
     public function setMatcherFactory(MatcherFactory $matcherFactory)
     {
         $this->_matcherFactory = $matcherFactory;
     }
     
     /**
      * Returns the Matcher Factory
      *
      *  @return PHPSpec\Matcher\MatcherFactory
      */
      public function getMatcherFactory()
      {
          if ($this->_matcherFactory === null) {
              $this->_matcherFactory = new MatcherFactory;
          }
          return $this->_matcherFactory;
      }
      
      /**
       * Checks if the example group behaves like a shared example
       *
       * @return boolean
       */
      public function behavesLikeAnotherObject()
      {
          if (!empty($this->itBehavesLike)) {
              if (!is_subclass_of(
                      $this->itBehavesLike,
                      '\PHPSpec\Specification\SharedExample'
                  )) {
                  throw new SpecificationException(
                      "$this->itBehavesLike is not a SharedExample"
                  );
              }
              return true;
          }
          return false;
      }
      
      /**
       * Returns the shared examples classes this example group behaves like
       *
       * @return string|array
       */
      public function getBehavesLike()
      {
          return $this->itBehavesLike;
      }
      
      /**
       * Adds a shared example to example group
       *
       * @param SharedExample $sharedExample 
       * @param string $method
       */
      public function addSharedExample(SharedExample $sharedExample, $method)
      {
          $shared = &$this->sharedExamples[$method];
          $shared['closure'] = function() use ($sharedExample, $method) {
              $sharedExample->$method();
          };
          $shared['sharedExample'] = $sharedExample;
      }
      
      /**
       * Checks to see an example exists in a shared example
       *
       * @param string $example 
       * @return boolean
       */
      public function hasSharedExample($example)
      {
          return isset($this->sharedExamples[$example]);
      }
      
      /**
       * The the shared example for a example
       *
       * @param string $example 
       * @return SharedExample
       */
      public function getSharedExample($example)
      {
          return $this->sharedExamples[$example]['sharedExample'];
      }
      
      /**
       * Runs the Shared Exammple
       *
       * @param string $example
       */
      public function runSharedExample($example)
      {
          $sharedExample = $this->getSharedExample($example);
          if (method_exists($sharedExample, 'before')) {
              call_user_func(array($sharedExample, 'before'));
          }
          
          $this->sharedExamples[$example]['closure']();
          
          if (method_exists($sharedExample, 'after')) {
              call_user_func(array($sharedExample, 'after'));
          }
      }
}
