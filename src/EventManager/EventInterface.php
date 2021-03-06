<?php
namespace Imbo\EventManager;

use Imbo\Http\Request\Request;
use Imbo\Http\Response\Response;
use Imbo\Database\DatabaseInterface;
use Imbo\Auth\AccessControl\Adapter\AdapterInterface as AccessControlInterface;
use Imbo\Storage\StorageInterface;
use Imbo\Image\OutputConverterManager;
use Imbo\Image\TransformationManager;
use Imbo\Image\InputLoaderManager;

interface EventInterface {
    /**
     * Get the event name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the event name
     *
     * @param string $name
     * @return self
     */
    public function setName($name);

    /**
     * Check if propagation has been stopped
     *
     * @return boolean
     */
    public function isPropagationStopped();

    /**
     * Stops the propagation of the event
     */
    public function stopPropagation();

    /**
     * Get argument
     *
     * @param string $key
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function getArgument($key);

    /**
     * Add argument
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setArgument($key, $value);

    /**
     * Set arguments
     *
     * @param array $arguments
     * @return self
     */
    public function setArguments(array $arguments = []);

    /**
     * See if the event has an argument
     *
     * @param string $key
     * @return boolean
     */
    public function hasArgument($key);

    /**
     * Get the request parameter
     *
     * @return Request
     */
    function getRequest();

    /**
     * Get the response parameter
     *
     * @return Response
     */
    function getResponse();

    /**
     * Get the database adapter
     *
     * @return DatabaseInterface
     */
    function getDatabase();

    /**
     * Get the storage adapter
     *
     * @return StorageInterface
     */
    function getStorage();

    /**
     * Get the access control adapter
     *
     * @return AccessControlInterface
     */
    function getAccessControl();

    /**
     * Get the event manager that triggered the event
     *
     * @return EventManager
     */
    function getManager();

    /**
     * Get the image transformation manager
     *
     * @return TransformationManager
     */
    function getTransformationManager();

    /**
     * Get the image output converter manager
     *
     * @return OutputConverterManager
     */
    function getOutputConverterManager();

    /**
     * Get the image loader manager
     *
     * @return InputLoaderManager
     */
    function getInputLoaderManager();

    /**
     * Get the Imbo configuration
     *
     * @return array
     */
    function getConfig();

    /**
     * Get the handler for the current event
     *
     * @return string
     */
    function getHandler();
}
