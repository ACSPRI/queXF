<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: TestResult.php 2 2009-03-16 20:22:51Z ggiunta $
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

require_once 'PHPUnit/TestFailure.php';
require_once 'PHPUnit/TestListener.php';

if (!function_exists('is_a')) {
    require_once 'PHP/Compat/Function/is_a.php';
}

/**
 * A TestResult collects the results of executing a test case.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_TestResult {
    /**
     * @var    array
     * @access protected
     */
    var $_errors = array();

    /**
     * @var    array
     * @access protected
     */
    var $_failures = array();

     /**
     * @var    array
     * @access protected
     */
    var $_listeners = array();

    /**
     * @var    array
     * @access protected
     */
    var $_passedTests = array();

    /**
     * @var    integer
     * @access protected
     */
    var $_runTests = 0;

    /**
     * @var    boolean
     * @access private
     */
    var $_stop = FALSE;

    /**
     * Adds an error to the list of errors.
     * The passed in exception caused the error.
     *
     * @param  object
     * @param  object
     * @access public
     */
    function addError(&$test, &$t) {
        $this->_errors[] = new PHPUnit_TestFailure($test, $t);

        for ($i = 0; $i < sizeof($this->_listeners); $i++) {
            $this->_listeners[$i]->addError($test, $t);
        }
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  object
     * @param  object
     * @access public
     */
    function addFailure(&$test, &$t) {
        $this->_failures[] = new PHPUnit_TestFailure($test, $t);

        for ($i = 0; $i < sizeof($this->_listeners); $i++) {
            $this->_listeners[$i]->addFailure($test, $t);
        }
    }

    /**
     * Registers a TestListener.
     *
     * @param  object
     * @access public
     */
    function addListener(&$listener) {
        if (is_object($listener) &&
            is_a($listener, 'PHPUnit_TestListener')) {
            $this->_listeners[] = &$listener;
        }
    }

    /**
     * Adds a passed test to the list of passed tests.
     *
     * @param  object
     * @access public
     */
    function addPassedTest(&$test) {
        $this->_passedTests[] = &$test;
    }

    /**
     * Informs the result that a test was completed.
     *
     * @param  object
     * @access public
     */
    function endTest(&$test) {
        for ($i = 0; $i < sizeof($this->_listeners); $i++) {
            $this->_listeners[$i]->endTest($test);
        }
    }

    /**
     * Gets the number of detected errors.
     *
     * @return integer
     * @access public
     */
    function errorCount() {
        return sizeof($this->_errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     * @access public
     */
    function &errors() {
        return $this->_errors;
    }

    /**
     * Gets the number of detected failures.
     *
     * @return integer
     * @access public
     */
    function failureCount() {
        return sizeof($this->_failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     * @access public
     */
    function &failures() {
        return $this->_failures;
    }

    /**
     * Returns an Enumeration for the passed tests.
     *
     * @return array
     * @access public
     */
    function &passedTests() {
        return $this->_passedTests;
    }

    /**
     * Unregisters a TestListener.
     * This requires the Zend Engine 2 (to work properly).
     *
     * @param  object
     * @access public
     */
    function removeListener(&$listener) {
        for ($i = 0; $i < sizeof($this->_listeners); $i++) {
            if ($this->_listeners[$i] === $listener) {
                unset($this->_listeners[$i]);
            }
        }
    }

    /**
     * Runs a TestCase.
     *
     * @param  object
     * @access public
     */
    function run(&$test) {
        $this->startTest($test);
        $this->_runTests++;
        $test->runBare();
        $this->endTest($test);
    }

    /**
     * Gets the number of run tests.
     *
     * @return integer
     * @access public
     */
    function runCount() {
        return $this->_runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @access public
     */
    function shouldStop() {
        return $this->_stop;
    }

    /**
     * Informs the result that a test will be started.
     *
     * @param  object
     * @access public
     */
    function startTest(&$test) {
        for ($i = 0; $i < sizeof($this->_listeners); $i++) {
            $this->_listeners[$i]->startTest($test);
        }
    }

    /**
     * Marks that the test run should stop.
     *
     * @access public
     */
    function stop() {
        $this->_stop = TRUE;
    }

    /**
     * Returns a HTML representation of the test result.
     *
     * @return string
     * @access public
     */
    function toHTML() {
        return '<pre>' . htmlspecialchars($this->toString()) . '</pre>';
    }

    /**
     * Returns a text representation of the test result.
     *
     * @return string
     * @access public
     */
    function toString() {
        $result = '';

        foreach ($this->_passedTests as $passedTest) {
            $result .= sprintf(
              "TestCase %s->%s() passed\n",

              get_class($passedTest),
              $passedTest->getName()
            );
        }

        foreach ($this->_failures as $failedTest) {
            $result .= $failedTest->toString();
        }

        return $result;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return boolean
     * @access public
     */
    function wasSuccessful() {
        if (empty($this->_errors) && empty($this->_failures)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
