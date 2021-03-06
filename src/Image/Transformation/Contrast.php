<?php
namespace Imbo\Image\Transformation;

use Imbo\Exception\TransformationException;
use ImagickException;

/**
 * Contrast transformation
 */
class Contrast extends Transformation {
    /**
     * {@inheritdoc}
     */
    public function transform(array $params) {
        $alpha = isset($params['sharpen']) ? (float) $params['sharpen'] : 1;
        $alpha = isset($params['alpha']) ? (float) $params['alpha'] : $alpha;
        $beta = isset($params['beta']) ? (float) $params['beta'] : 0.5;
        $sharpen = $alpha > 0;

        if ($alpha == 0) {
            return;
        }

        $beta *= $this->getQuantumRange();

        try {
            $this->imagick->sigmoidalContrastImage($sharpen, abs($alpha), $beta);
        } catch (ImagickException $e) {
            throw new TransformationException($e->getMessage(), 400, $e);
        }

        $this->image->setHasBeenTransformed(true);
    }
}
