<?php

/**
 * This file is part of the EmailReplyParser package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace EmailReplyParser;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Email
{
    /**
     * @var array
     */
    protected $fragments = array();

    /**
     * Read a text which represents an email and splits it into fragments.
     *
     * @param string $text  A text.
     * @return array
     */
    public function read($text)
    {
        if (preg_match('/^(On(.+)wrote:)$/ms', $text, $matches)) {
            $text = str_replace($matches[1], str_replace("\n", ' ', $matches[1]), $text);
        }

        $lines = explode("\n", strrev($text));

        $fragment = null;
        $isQuoted = false;
        $foundVisible = false;

        foreach ($lines as $line) {
            $line = preg_replace("/\n$/", '', ltrim($line));

            // isQuoted ?
            $isQuoted = preg_match('/(>+)$/', $line) ? true : false;

            if (null !== $fragment && empty($line)) {
                if (preg_match('/(--|__|\w-$)|(^(\w+\s*){1,3} ym morf tneS$)/', $fragment->getLastLine())) {
                    $fragment->setIsSignature(true);

                    if (!$foundVisible) {
                        if ($fragment->isQuoted() || $fragment->isSignature() || $fragment->isEmpty()) {
                            $fragment->setIsHidden(true);
                        } else {
                            $foundVisible = true;
                        }
                    }

                    $this->fragments[] = $fragment;
                    $fragment = null;
                }
            }

            if (null !== $fragment && (($isQuoted === $fragment->isQuoted()) || preg_match('/^:etorw.*nO$/', $line) || empty($line))) {
                $fragment->addLine($line);
            } else {
                if (null !== $fragment) {
                    if (!$foundVisible) {
                        if ($fragment->isQuoted() || $fragment->isSignature() || $fragment->isEmpty()) {
                            $fragment->setIsHidden(true);
                        } else {
                            $foundVisible = true;
                        }
                    }

                    $this->fragments[] = $fragment;
                }
                $fragment = null;
                $fragment = new Fragment($line, $isQuoted);
            }
        }

        if (null !== $fragment) {
            if (!$foundVisible) {
                if ($fragment->isQuoted() || $fragment->isSignature() || $fragment->isEmpty()) {
                    $fragment->setIsHidden(true);
                } else {
                    $foundVisible = true;
                }
            }

            $this->fragments[] = $fragment;
        }

        $this->fragments = array_reverse($this->fragments);
        return $this->fragments;
    }

    /**
     * Returns an array of fragments.
     *
     * @return array
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * @return string
     */
    public function getVisibleText()
    {
        $visibleFragments = array_filter($this->getFragments(), function($fragment) {
            return !$fragment->isHidden();
        });

        return rtrim(implode("\n", $visibleFragments));
    }
}
