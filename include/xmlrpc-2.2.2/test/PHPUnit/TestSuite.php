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
 * @version    CVS: $Id: TestSuite.php 2 2009-03-16 20:22:51Z ggiunta $
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

require_once 'PHPUnit/TestCase.php';

/**
 * A TestSuite is a Composite of Tests. It runs a collection of test cases.
 *
 * Here is an example using the dynamic test definition.
 *
 * <code>
 * <?php
 * $suite = new PHPUnit_TestSuite();
 * $suite->addTest(new MathTest('testPass'));
 * ?>
 * </code>
 *
 * Alternatively, a TestSuite can extract the tests to be run automatically.
 * To do so you pass the classname of your TestCase class to the TestSuite
 * constructor.
 *
 * <code>
 * <?php
 * $suite = new TestSuite('classname');
 * ?>
 * </code>
 *
 * This constructor creates a suite with all the methods starting with
 * "test" that take no arguments.
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
class PHPUnit_TestSuite {
    /**
     * The name of the test suite.
     *
     * @var    string
     * @access private
     */
    var $_name = '';

    /**
     * The tests in the test suite.
     *
     * @var    array
     * @access private
     */
    var $_tests = array();

    /**
     * Constructs a TestSuite.
     *
     * @param  mixed
     * @access public
     */
    function PHPUnit_TestSuite($test = FALSE) {
        if ($test !== FALSE) {
            $this->setName($test);
            $this->addTestSuite($test);
        }
    }

    /**
     * Adds a test to the suite.
     *
     * @param  object
     * @access public
     */
    function addTest(&$test) {
        $this->_tests[] = &$test;
    }

    /**
     * Adds the tests from the given class to the suite.
     *
     * @param  string
     * @access public
     */
    function addTestSuite($testClass) {
        if (class_exists($testClass)) {
            $methods       = get_class_methods($testClass);
            $parentClasses = array(strtolower($testClass));
            $parentClass   = $testClass;

            while(is_string($parentClass = get_parent_class($parentClass))) {
                $parentClasses[] = $parentClass;
            }

            foreach ($methods as $method) {
                if (substr($method, 0, 4) == 'test' &&
                    !in_array($method, $parentClasses)) {
                    $this->addTest(new $testClass($method));
                }
            }
        }
    }

    /**
     * Counts the number of test cases that will be run by this test.
     *
     * @return integer
     * @access public
     */
    function countTestCases() {
        $count = 0;

        foreach ($this->_tests as $test) {
            $count += $test->countTestCases();
        }

        return $count;
    }

    /**
     * Returns the name of the suite.
     *
     * @return string
     * @access public
     */
    function getName() {
        return $this->_name;
    }

    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @param  object
     * @access public
     */
    function run(&$result, $show_progress='') {
        for ($i = 0; $i < sizeof($this->_tests) && !$result->shouldStop(); $i++) {
            $this->_tests[$i]->run($result);
            if ($show_progress != '') {
                echo $show_progress; flush();
            }
        }
    }

    /**
     * Runs a test.
     *
     * @param  object
     * @param  object
     * @access public
     */
    function runTest(&$test, &$result) {
        $test->run($result);
    }

    /**
     * Sets the name of the suite.
     *
     * @param  string
     * @access public
     */
    function setName($name) {
        $this->_name = $name;
    }

    /**
     * Returns the test at the given index.
     *
     * @param  integer
     * @return object
     * @access public
     */
    function &testAt($index) {
        if (isset($this->_tests[$index])) {
            return $this->_tests[$index];
        } else {
            return FALSE;
        }
    }

    /**
     * Returns the number of tests in this suite.
     *
     * @return integer
     * @access public
     */
    function testCount() {
        return sizeof($this->_tests);
    }

    /**
     * Returns the tests as an enumeration.
     *
     * @return array
     * @access public
     */
    function &tests() {
        return $this->_tests;
    }

    /**
     * Returns a string representation of the test suite.
     *
     * @return string
     * @access public
     */
    function toString() {
        return '';
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
