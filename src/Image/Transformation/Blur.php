<?php
namespace Imbo\Image\Transformation;

use Imbo\Exception\TransformationException;
use ImagickException;

/**
 * Blur transformation
 */
class Blur extends Transformation {
    /**
     * {@inheritdoc}
     */
    public function transform(array $params) {
        $type = isset($params['type']) ? $params['type'] : 'gaussian';

        $blurTypes = ['gaussian', 'adaptive', 'motion', 'radial', 'rotational'];

        if (!in_array($type, $blurTypes)) {
            throw new TransformationException('Unknown blur type: ' . $type, 400);
        }

        switch ($type) {
            case 'motion':
                return $this->motionBlur($params);

            case 'rotational':
            case 'radial':
                return $this->rotationalBlur($params);

            case 'adaptive':
                return $this->blur($params, true);

            default:
                return $this->blur($params);
        }
    }

    /**
     * Check that all params are present
     *
     * @param array $params Transformation parameter list
     * @param array $required Array with required parameters
     * @throws TransformationException
     */
    private function checkRequiredParams(array $params, array $required) {
        foreach ($required as $param) {
            if (!isset($params[$param])) {
                throw new TransformationException('Missing required parameter: ' . $param, 400);
            }
        }
    }

    /**
     * Add Gaussian or adaptive blur to the image
     *
     * @param array $params The transformation parameters
     * @param bool $adaptive Perform adaptive blur or not
     */
    private function blur(array $params, $adaptive = false) {
        $this->checkRequiredParams($params, ['radius', 'sigma']);

        $radius = (float) $params['radius'];
        $sigma = (float) $params['sigma'];

        try {
            if ($adaptive) {
                $this->imagick->adaptiveBlurImage($radius, $sigma);
            } else {
                $this->imagick->gaussianBlurImage($radius, $sigma);
            }
        } catch (ImagickException $e) {
            throw new TransformationException($e->getMessage(), 400, $e);
        }

        $this->image->setHasBeenTransformed(true);
    }

    /**
     * Add motion blur to the image
     *
     * @param array $params The transformation parameters
     */
    private function motionBlur(array $params) {
        $this->checkRequiredParams($params, ['radius', 'sigma', 'angle']);

        $radius = (float) $params['radius'];
        $sigma = (float) $params['sigma'];
        $angle = (float) $params['angle'];

        try {
            $this->imagick->motionBlurImage($radius, $sigma, $angle);
        } catch (ImagickException $e) {
            throw new TransformationException($e->getMessage(), 400, $e);
        }

        $this->image->setHasBeenTransformed(true);
    }

    /**
     * Add rotational blur to the image
     *
     * @param array $params The transformation parameters
     */
    private function rotationalBlur(array $params) {
        $this->checkRequiredParams($params, ['angle']);

        $angle = (float) $params['angle'];

        try {
            $this->imagick->rotationalBlurImage($angle);
        } catch (ImagickException $e) {
            throw new TransformationException($e->getMessage(), 400, $e);
        }

        $this->image->setHasBeenTransformed(true);
    }
}
