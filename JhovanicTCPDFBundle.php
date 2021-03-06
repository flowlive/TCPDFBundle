<?php

namespace JhovaniC\TCPDFBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class JhovanicTCPDFBundle extends Bundle
{
    /**
     * Ran on bundle boot, our TCPDF configuration constants
     * get defined here if required
     */
    public function boot()
    {
        if (!$this->container->hasParameter('jhovanic_tcpdf.tcpdf'))
            return;

        // Define our TCPDF variables
        $config = $this->container->getParameter('jhovanic_tcpdf.tcpdf');

        // TCPDF needs some constants defining if our configuration
        // determines we should do so (default true)
        // Set tcpdf.k_tcpdf_external_config to false to use the TCPDF
        // core defaults
        if ($config['k_tcpdf_external_config'])
        {
            foreach ($config as $k => $v)
            {
                $constKey = strtoupper($k);

                // All K_ constants are required
                if (preg_match("/^k_/i", $k))
                {
                    if (!defined($constKey))
                    {
                        $value = $this->container->getParameterBag()->resolveValue($v);

                        if (($k === 'k_path_cache' || $k === 'k_path_url_cache') && !is_dir($value)) {
                            $this->createDir($value);
                        }

                        // Added trailing slash because somehow the yml parser
                        // removes slashes at the end
                        if ($k === 'k_path_fonts' && is_dir($value)) {
                            $value = $value . '/';
                        }

                        if ($k === 'k_path_images' && is_dir($value)) {
                            $value = $value . '/';
                        }

                        define($constKey, $value);
                    }
                }

                // All pdf_ values defined in config.yml
                if (preg_match("/^pdf_/i", $k)) {
                    if (!defined($constKey)) {
                        define($constKey, $v);
                    }
                }

                // and one special value which TCPDF will use if present
                if (strtolower($k) == "pdf_font_name_main" && !defined($constKey))
                {
                    define($constKey, $v);
                }
            }
        }
    }

    /**
     * Create a directory
     *
     * @param string $filePath
     *
     * @throws \RuntimeException
     */
    private function createDir($filePath)
    {
        $filesystem = new Filesystem();
        if (false === $filesystem->mkdir($filePath)) {
            throw new \RuntimeException(sprintf(
                'Could not create directory %s', $filePath
            ));
        }
    }
}
