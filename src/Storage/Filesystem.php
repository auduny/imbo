<?php declare(strict_types=1);
namespace Imbo\Storage;

use Imbo\Exception\StorageException;
use Imbo\Exception;
use DateTime;
use DateTimeZone;

/**
 * Filesystem storage driver
 *
 * This storage driver stores image files in a local filesystem.
 *
 * Configuration options supported by this driver:
 *
 * - <pre>(string) dataDir</pre> Absolute path to the base directory the images should be stored in
 */
class Filesystem implements StorageInterface {
    /**
     * Parameters for the filesystem driver
     *
     * @var array
     */
    private array $params = [
        'dataDir' => null,
    ];

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     */
    public function __construct(array $params) {
        $this->params = array_merge($this->params, $params);

        if (empty($this->params['dataDir'])) {
            throw new Exception\ConfigurationException(
                'Missing required parameter dataDir in the Filesystem storage driver.',
                500
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function store(string $user, string $imageIdentifier, string $imageData) : bool {
        if (!is_writable($this->getParams()['dataDir'])) {
            throw new StorageException('Could not store image', 500);
        }

        if ($this->imageExists($user, $imageIdentifier)) {
            return touch($this->getImagePath($user, $imageIdentifier));
        }

        $imageDir = $this->getImagePath($user, $imageIdentifier, false);
        $oldUmask = umask(0);

        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0775, true);
        }

        umask($oldUmask);

        $imagePath = $imageDir . '/' . $imageIdentifier;

        // write the file to .tmp, so we can do an atomic rename later to avoid possibly serving partly written files
        $bytesWritten = file_put_contents($imagePath . '.tmp', $imageData);

        // if write failed or 0 bytes were written (0 byte input == fail), or we wrote less than expected
        if (!$bytesWritten || ($bytesWritten < strlen($imageData))) {
            throw new StorageException('Failed writing file (disk full? zero bytes input?) to disk: ' . $imagePath, 507);
        }

        rename($imagePath . '.tmp', $imagePath);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $user, string $imageIdentifier) : bool {
        if (!$this->imageExists($user, $imageIdentifier)) {
            throw new StorageException('File not found', 404);
        }

        $path = $this->getImagePath($user, $imageIdentifier);

        return unlink($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(string $user, string $imageIdentifier) : ?string {
        if (!$this->imageExists($user, $imageIdentifier)) {
            throw new StorageException('File not found', 404);
        }

        $path = $this->getImagePath($user, $imageIdentifier);

        return file_get_contents($path) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified(string $user, string $imageIdentifier) : DateTime {
        if (!$this->imageExists($user, $imageIdentifier)) {
            throw new StorageException('File not found', 404);
        }

        $path = $this->getImagePath($user, $imageIdentifier);

        // Get the unix timestamp
        $timestamp = filemtime($path);

        // Create a new datetime instance
        return new DateTime(sprintf('@%d', $timestamp), new DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus() : bool {
        return is_writable($this->getParams()['dataDir']);
    }

    /**
     * {@inheritdoc}
     */
    public function imageExists(string $user, string $imageIdentifier) : bool {
        $path = $this->getImagePath($user, $imageIdentifier);

        return file_exists($path);
    }

    /**
     * Get the set of params provided when creating the instance
     *
     * @return array<string>
     */
    protected function getParams() {
        return $this->params;
    }

    /**
     * Get the path to an image
     *
     * @param string $user The user which the image belongs to
     * @param string $imageIdentifier Image identifier
     * @param bool $includeFilename Whether or not to include the last part of the path (the
     *                                 filename itself)
     * @return string
     */
    protected function getImagePath(string $user, string $imageIdentifier, bool $includeFilename = true) : string {
        $userPath = str_pad($user, 3, '0', STR_PAD_LEFT);
        $parts = [
            $this->getParams()['dataDir'],
            $userPath[0],
            $userPath[1],
            $userPath[2],
            $user,
            $imageIdentifier[0],
            $imageIdentifier[1],
            $imageIdentifier[2],
        ];

        if ($includeFilename) {
            $parts[] = $imageIdentifier;
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
