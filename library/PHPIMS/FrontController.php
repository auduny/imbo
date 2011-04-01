<?php
/**
 * PHPIMS
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package PHPIMS
 * @subpackage Server
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */

/**
 * Client that interacts with the server part of PHPIMS
 *
 * This client includes methods that can be used to easily interact with a PHPIMS server
 *
 * @package PHPIMS
 * @subpackage Server
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */
class PHPIMS_FrontController {
    /**#@+
     * Supported HTTP methods
     *
     * @var string
     */
    const GET    = 'GET';
    const POST   = 'POST';
    const HEAD   = 'HEAD';
    const DELETE = 'DELETE';
    const BREW   = 'BREW';
    /**#@-*/

    /**
     * Configuration
     *
     * @var array
     */
    protected $config = null;

    /**
     * Class constructor
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config = null) {
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    /**
     * Wether or not the method is valid
     *
     * @param string $method The current method
     * @return boolean True if $method is valid, false otherwise
     */
    static public function isValidMethod($method) {
        switch ($method) {
            case self::GET:
            case self::POST:
            case self::HEAD:
            case self::DELETE:
            case self::BREW:
                return true;
            default:
                return false;
        }
    }

    /**
     * Get the config array
     *
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Set the config array
     *
     * @param array $config The configuration array to set
     * @return PHPIMS_FrontController
     */
    public function setConfig(array $config) {
        $this->config = $config;

        return $this;
    }

    /**
     * Generate an operation object based on some parameters
     *
     * Valid requests are: (GET|POST|DELETE|HEAD) /<hash>[/meta]
     *
     * @param string $method The HTTP method
     * @param string $hash   Image hash (md5)
     * @param string $extra  Optional extra argument
     * @throws PHPIMS_Exception
     * @return PHPIMS_Operation_Abstract
     */
    protected function resolveOperation($method, $hash, $extra = null) {
        $operation = null;

        if ($method === self::GET) {
            if ($extra === 'meta') {
                $operation = 'PHPIMS_Operation_GetMetadata';
            } else if (empty($extra)) {
                $operation = 'PHPIMS_Operation_GetImage';
            }
        } else if ($method === self::POST) {
            if ($extra === 'meta') {
                $operation = 'PHPIMS_Operation_EditMetadata';
            } else {
                $operation = 'PHPIMS_Operation_AddImage';
            }
        } else if ($method === self::DELETE) {
            if ($extra === 'meta') {
                $operation = 'PHPIMS_Operation_DeleteMetadata';
            } else {
                $operation = 'PHPIMS_Operation_DeleteImage';
            }
        } else if ($method === self::BREW) {
            throw new PHPIMS_Exception('I\'m a teapot!', 418);
        }

        if ($operation === null) {
            throw new PHPIMS_Exception('Unsupported operation', 400);
        }

        $factoryClass = $this->config['operation']['factory'];

        return $factoryClass::factory($operation, $method, $hash);
    }

    /**
     * Handle a request
     *
     * @param string $method The HTTP method (one of the defined constants)
     * @param string $path The path requested
     * @throws PHPIMS_Exception
     */
    public function handle($method, $path) {
        if (!self::isValidMethod($method)) {
            throw new PHPIMS_Exception($method . ' not implemented', 501);
        }

        $matches = array();

        if (!preg_match('|(?<hash>[a-f0-9]{32}\.[a-zA-Z]{3,4})(?:/(?<extra>.*))?$|', $path, $matches)) {
            throw new PHPIMS_Exception('Invalid request: ' . $path, 400);
        }

        $hash = $matches['hash'];
        $extra = isset($matches['extra']) ? $matches['extra'] : null;

        $operation = $this->resolveOperation($method, $hash, $extra);
        $operation->init($this->config)
                  ->preExec()
                  ->exec()
                  ->postExec();

        return $operation->getResponse();
    }
}