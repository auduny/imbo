<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\Image\InputLoader;

/**
 * TIFF image loader
 *
 * @author Mats Lindh <mats@lindh.no>
 * @package Image\Loaders
 */
class Tiff implements InputLoaderInterface {
    /**
     * {@inheritdoc}
     */
    public function getMimeTypeCallbacks() {
        return [
            'image/tiff' => [
                'extension' => 'tif',
                'callback' => [$this, 'load'],
            ],
        ];
    }

    /**
     * Load the image
     *
     * @param Imagick $imagick
     * @param string $blob
     * @return Imagick
     */
    public function load($imagick, $blob) {
        $imagick->readImageBlob($blob);

        return $imagick;
    }
}