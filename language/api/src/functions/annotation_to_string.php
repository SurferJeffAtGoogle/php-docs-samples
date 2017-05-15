<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Language;

use Google\Cloud\NaturalLanguage\Annotation;

function generate_annotation_printer($annotation) {
    $ret = <<<EOS
function annotation_to_string(Annotation \$annotation)
{
    \$ret = '';
    \$info = \$annotation->info();
EOS;
    $ret .= generate_array_dump('    ', 'info', $annotation->info());
    $ret .= '}';
    return $ret;
}

function collect_key_paths_recursively(array $array, array $ancestorKeyPath, array &$keyPaths) {
    foreach ($array as $key => $value) {
        if ($key === 0) {
            $key = '#';
        }
        $path = $ancestorKeyPath;
        if (!is_array($value)) {
            $key .= '$';
        }
        array_push($path, $key);
        $keyPaths[implode('-',$path)] = true;
        if (is_array($value)) {
            collect_key_paths_recursively($value, $path, $keyPaths);
        }
        if ($key === '#') {
            break;
        }
    }
}

function count_array_matches(array $a, array $b) {
    $max = min(count($a), count($b));
    for ($i = 0; $i < $max; ++$i) {
        if ($a[$i] != $b[$i]) {
            break;
        }
    }
    return $i;
}

function generate_annotation_to_string(Annotation $annotation) {
    $text = '';
    $indent = '    ';
    $keyPaths = collect_key_paths($annotation->info());
    /** @var string $path */
    $prevSteps = [];
    foreach($keyPaths as $path) {
        $steps = explode('-', $path);
        $n = count_array_matches($prevSteps, $steps);
        while ($n < count($prevSteps)) {
            $indent = substr($indent, 0, count($indent) - 4);
            $text .= $indent . '}' . PHP_EOL;
            array_pop($prevSteps);
        }
        while ($n < count($steps)) {
            array_push($prevSteps, $steps[$n]);
            $keyLiterals = [];
            foreach ($prevSteps as $step) {
                array_push($keyLiterals, var_export($step, true));
            }
            $text .= $indent . sprintf('if (isset($info[%s]) {',
                implode('][', $keyLiterals)) . PHP_EOL;
            $indent .= '   ';
            $n += 1;
        }
    }
    while (0 < count($prevSteps)) {
        $indent = substr($indent, 0, count($indent) - 4);
        $text .= $indent . '}' . PHP_EOL;
        array_pop($prevSteps);
    }
    return $text;
}

function collect_key_paths(array $array)
{
    $keyPaths = [];
    collect_key_paths_recursively($array, [], $keyPaths);
    $keyPaths = array_keys($keyPaths);
    sort($keyPaths);
    return $keyPaths;
}

function generate_dump($indent, $arrayName, $key, $value) {
    $keyLiteral = var_export($key, true);
    $text = $indent . sprintf('if (isset($%s[%s])) {', $arrayName, $keyLiteral)
        . PHP_EOL;
    $innerIndent = $indent . '    ';
    if (is_array($value)) {
        $text .= $innerIndent . sprintf('$%s = %s[%s]',$key, $arrayName, $keyLiteral)
            . PHP_EOL;
        $text .= generate_array_dump($innerIndent, $key, $value);
    } else {
        $text .= $innerIndent . '$ret .= sprintf("%s: %s",' . PHP_EOL;
        $text .= $innerIndent . "  $keyLiteral, $arrayName[$keyLiteral]);" . PHP_EOL;
    }
    $text .= $indent . '}' . PHP_EOL;
    return $text;
}

function generate_array_dump($indent, $arrayName, $array) {
    $text = '';
    foreach ($array as $innerKey => $innerValue) {
        $text .= generate_dump($indent, $arrayName, $innerKey, $innerValue);
    }
    return $text;
}

/**
 * Convert an Annotation to a string.
 *
 * @param Annotation $annotation
 * @return string
 */
function annotation_to_string(Annotation $annotation)
{
    echo generate_annotation_to_string($annotation);
    return;
    $ret = '';
    $info = $annotation->info();
    if (isset($info['language'])) {
        $ret .= sprintf('language: %s' . PHP_EOL, $info['language']);
    }

    if (isset($info['documentSentiment'])) {
        $magnitude = $info['documentSentiment']['magnitude'];
        $score = $info['documentSentiment']['score'];
        $ret .= sprintf('sentiment magnitude: %s score: %s' . PHP_EOL,
            $magnitude, $score);
    }

    if (isset($info['sentences'])) {
        $ret .= 'sentences:' . PHP_EOL;
        foreach ($info['sentences'] as $sentence) {
            $ret .= sprintf('  %s: %s' . PHP_EOL,
                $sentence['text']['beginOffset'],
                $sentence['text']['content']);
        }
    }

    if (isset($info['tokens'])) {
        $ret .= 'tokens:' . PHP_EOL;
        foreach ($info['tokens'] as $token) {
            $ret .= sprintf('  %s: %s' . PHP_EOL,
                $token['lemma'],
                $token['partOfSpeech']['tag']);
        }
    }

    if (isset($info['entities'])) {
        $ret .= 'entities:' . PHP_EOL;
        foreach ($info['entities'] as $entity) {
            $ret .= sprintf('  %s', $entity['name']);
            if (isset($entity['metadata']['wikipedia_url'])) {
                $ret .= sprintf(': %s', $entity['metadata']['wikipedia_url']);
            }
            $ret .= PHP_EOL;
        }
    }
    return $ret;
}
