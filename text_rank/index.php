<?php



declare(strict_types=1);

/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */

declare(strict_types=1);

namespace PhpScience\TextRank\Tool;

/**
 * Class Summarize
 *
 * This is for summarize the text from parsed data.
 *
 * @package PhpScience\TextRank\Tool
 */
class Summarize
{
    /**
     * To find all important sentences.
     *
     * @var int
     */
    const GET_ALL_IMPORTANT = 0;

    /**
     * To find the most important sentence and its following sentences.
     *
     * @var int
     */
    const GET_FIRST_IMPORTANT_AND_FOLLOWINGS = 1;

    /**
     * Array of sentence weight. Key is the index of the sentence and value is
     * the weight of the sentence.
     *
     * @var array
     */
    protected $sentenceWeight = [];

    /**
     * Summarize text.
     *
     * It retrieves the summarized text in array.
     *
     * @param array $scores        Keywords with scores. Score is the key.
     * @param Graph $graph         The graph of the text.
     * @param Text  $text          Text object what stores all text data.
     * @param int   $keyWordLimit  How many keyword should be used to find the
     *                             important sentences.
     * @param int   $sentenceLimit How many sentence should be retrieved.
     * @param int   $type          The type of summarizing. Possible values are
     *                             the constants of this class.
     *
     * @return array An array from sentences.
     */
    public function getSummarize(
        array &$scores,
        Graph &$graph,
        Text &$text,
        int $keyWordLimit,
        int $sentenceLimit,
        int $type
    ): array {

        $graphData = $graph->getGraph();
        $sentences = $text->getSentences();
        $marks = $text->getMarks();
        $this->findAndWeightSentences($scores, $graphData, $keyWordLimit);

        if ($type == Summarize::GET_ALL_IMPORTANT) {
            return $this->getAllImportant($sentences, $marks, $sentenceLimit);

        } else if ($type == Summarize::GET_FIRST_IMPORTANT_AND_FOLLOWINGS) {
            return $this->getFirstImportantAndFollowings(
                $sentences,
                $marks,
                $sentenceLimit
            );
        }

        return [];
    }

    /**
     * Find and Weight Sentences.
     *
     * It finds the most important sentences and stores them into the property.
     *
     * @param array $scores       Keywords with scores. Score is the key.
     * @param array $graphData    Graph data from a Graph type object.
     * @param int   $keyWordLimit How many keyword should be used to find the
     *                            important sentences.
     */
    protected function findAndWeightSentences(
        array &$scores,
        array &$graphData,
        int $keyWordLimit
    ) {
        $i = 0;

        foreach ($scores as $word => $score) {
            if ($i >= $keyWordLimit) {
                break;
            }

            $i++;
            $wordMap = $graphData[$word];

            foreach ($wordMap as $key => $value) {
                $this->updateSentenceWeight($key);
            }
        }

        arsort($this->sentenceWeight);
    }

    /**
     * Important Sentences.
     *
     * It retrieves the important sentences.
     *
     * @param array $sentences     Sentences, ordered by weights.
     * @param array $marks         Array of punctuations. Key is the reference
     *                             to the sentence, value is the punctuation.
     * @param int   $sentenceLimit How many sentence should be retrieved.
     *
     * @return array An array from sentences what are the most important
     *               sentences.
     */
    protected function getAllImportant(
        array &$sentences,
        array &$marks,
        int $sentenceLimit
    ): array {

        $summary = [];
        $i = 0;

        foreach ($this->sentenceWeight as $sentenceIdx => $weight) {
            if ($i >= $sentenceLimit) {
                break;
            }

            $i++;
            $summary[$sentenceIdx] = $sentences[$sentenceIdx]
                . $this->getMark($marks, $sentenceIdx);
        }

        ksort($summary);

        return $summary;
    }

    /**
     * Most Important Sentence and Next.
     *
     * It retrieves the first most important sentence and its following
     * sentences.
     *
     * @param array $sentences     Sentences, ordered by weights.
     * @param array $marks         Array of punctuations. Key is the reference
     *                             to the sentence, value is the punctuation.
     * @param int   $sentenceLimit How many sentence should be retrieved.
     *
     * @return array An array from sentences what contains the most important
     *               sentence and its following sentences.
     */
    protected function getFirstImportantAndFollowings(
        array &$sentences,
        array &$marks,
        int $sentenceLimit
    ): array {

        $summary = [];
        $startIdx = 0;

        foreach ($this->sentenceWeight as $sentenceIdx => $weight) {
            $summary[$sentenceIdx] = $sentences[$sentenceIdx] .
                $this->getMark($marks, $sentenceIdx);

            $startIdx = $sentenceIdx;
            break;
        }

        $i = 0;

        foreach ($sentences as $sentenceIdx => $sentence) {
            if ($sentenceIdx <= $startIdx) {
                continue;
            } else if ($i >= $sentenceLimit - 1) {
                break;
            }

            $i++;
            $summary[$sentenceIdx] = $sentences[$sentenceIdx] .
                $this->getMark($marks, $sentenceIdx);
        }

        return $summary;
    }

    /**
     * Update Sentence Weight.
     *
     * It updates the sentence weight what is stored in the property.
     *
     * @param int $sentenceIdx Index of the sentence.
     */
    protected function updateSentenceWeight(int $sentenceIdx)
    {
        if (isset($this->sentenceWeight[$sentenceIdx])) {
            $this->sentenceWeight[$sentenceIdx] = $this->sentenceWeight[$sentenceIdx] + 1;
        } else {
            $this->sentenceWeight[$sentenceIdx] = 1;
        }
    }

    /**
     * Punctuations.
     *
     * It retrieves the punctuation of the sentence.
     *
     * @param array $marks The punctuation. Key is the reference to the
     *                     sentence, value is the punctuation.
     * @param int   $idx   Key of the punctuation.
     *
     * @return string The punctuation of the sentence.
     */
    protected function getMark(array &$marks, int $idx)
    {
        return isset($marks[$idx]) ? $marks[$idx] : '';
    }
}


/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */


namespace PhpScience\TextRank\Tool;

/**
 * Class Score
 *
 * It handles words and assigns weighted numbers to them.
 *
 * @package PhpScience\TextRank\Tool
 */
class Score
{
    /**
     * The maximum connections by a word in the current text.
     *
     * @var int
     */
    protected $maximumValue = 0;

    /**
     * The minimum connection by a word in the current text.
     *
     * @var int
     */
    protected $minimumValue = 0;

    /**
     * Calculate Scores.
     *
     * It calculates the scores from word's connections and the connections'
     * scores. It retrieves the scores in a form of a matrix where the key is
     * the word and value is the score. The score is between 0 and 1.
     *
     * @param Graph $graph The graph of the text.
     * @param Text  $text  Text object what stores all text data.
     *
     * @return array Key is the word and value is the float or int type score
     *               between 1 and 0.
     */
    public function calculate(Graph $graph, Text &$text): array
    {
        $graphData = $graph->getGraph();
        $wordMatrix = $text->getWordMatrix();
        $wordConnections = $this->calculateConnectionNumbers($graphData);
        $scores = $this->calculateScores(
            $graphData,
            $wordMatrix,
            $wordConnections
        );

        return $this->normalizeAndSortScores($scores);
    }

    /**
     * Connection Numbers.
     *
     * It calculates the number of connections for each word and retrieves it
     * in array where key is the word and value is the number of connections.
     *
     * @param array $graphData Graph data from a Graph type object.
     *
     * @return array Key is the word and value is the number of the connected
     *               words.
     */
    protected function calculateConnectionNumbers(array &$graphData): array
    {
        $wordConnections = [];

        foreach ($graphData as $wordKey => $sentences) {
            $connectionCount = 0;

            foreach ($sentences as $sentenceIdx => $wordInstances) {
                foreach ($wordInstances as $connections) {
                    $connectionCount += count($connections);
                }
            }

            $wordConnections[$wordKey] = $connectionCount;
        }

        return $wordConnections;
    }

    /**
     * Calculate Scores.
     *
     * It calculates the score of the words and retrieves it in array where key
     * is the word and value is the score. The score depends on the number of
     * the connections and the closest word's connection numbers.
     *
     * @param array $graphData       Graph data from a Graph type object.
     * @param array $wordMatrix      Multidimensional array from integer keys
     *                               and string values.
     * @param array $wordConnections Key is the word and value is the number of
     *                               the connected words.
     *
     * @return array Scores where key is the word and value is the score.
     */
    protected function calculateScores(
        array &$graphData,
        array &$wordMatrix,
        array &$wordConnections
    ): array {
        $scores = [];

        foreach ($graphData as $wordKey => $sentences) {
            $value = 0;

            foreach ($sentences as $sentenceIdx => $wordInstances) {
                foreach ($wordInstances as $connections) {
                    foreach ($connections as $wordIdx) {
                        $word = $wordMatrix[$sentenceIdx][$wordIdx];
                        $value += $wordConnections[$word];
                    }
                }
            }

            $scores[$wordKey] = $value;

            if ($value > $this->maximumValue) {
                $this->maximumValue = $value;
            }

            if ($value < $this->minimumValue || $this->minimumValue == 0) {
                $this->minimumValue = $value;
            }
        }

        return $scores;
    }

    /**
     * Normalize and Sort Scores.
     *
     * It recalculates the scores by normalize the score numbers to between 0
     * and 1.
     *
     * @param array $scores Keywords with scores. Score is the key.
     *
     * @return array Keywords with normalized and ordered scores.
     */
    protected function normalizeAndSortScores(array &$scores): array
    {
        foreach ($scores as $key => $value) {
            $v = $this->normalize(
                $value,
                $this->minimumValue,
                $this->maximumValue
            );

            $scores[$key] = $v;
        }

        arsort($scores);

        return $scores;
    }

    /**
     * It normalizes a number.
     *
     * @param int $value Current weight.
     * @param int $min   Minimum weight.
     * @param int $max   Maximum weight.
     *
     * @return float|int Normalized weight aka score.
     */
    protected function normalize(int $value, int $min, int $max): float
    {
        $divisor = $max - $min;

        if ($divisor == 0) {
            return 0.0;
        }

        $normalized = ($value - $min) / $divisor;

        return $normalized;
    }
}

/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */



namespace PhpScience\TextRank\Tool;

/**
 * Class Graph
 *
 * This graph store the sentences and their words with the indexes. This graph
 * is the full map of the whole text.
 *
 * @package PhpScience\TextRank\Tool
 */
class Graph
{
    /**
     * Key is the word, value is an array with the sentence IDs.
     *
     * @var array
     */
    protected $graph = [];

    /**
     * Create Graph.
     *
     * It creates a graph and save it into the graph property.
     *
     * @param Text $text Text object contains the parsed and prepared text
     *                   data.
     */
    public function createGraph(Text &$text)
    {
        $wordMatrix = $text->getWordMatrix();

        foreach ($wordMatrix as $sentenceIdx => $words) {
            $idxArray = array_keys($words);

            foreach ($idxArray as $idxKey => $idxValue) {
                $connections = [];

                if (isset($idxArray[$idxKey - 1])) {
                    $connections[] = $idxArray[$idxKey - 1];
                }

                if (isset($idxArray[$idxKey + 1])) {
                    $connections[] = $idxArray[$idxKey + 1];
                }

                $this->graph[$words[$idxValue]][$sentenceIdx][$idxValue] = $connections;
            }
        }
    }

    /**
     * Graph.
     *
     * It retrieves the graph. Key is the word, value is an array with the
     * sentence IDs.
     *
     * <code>
     *       array(
     *           'apple' => array(    // word
     *               2 => array(      // ID of the sentence
     *                   52 => array( // ID of the word in the sentence
     *                       51, 53   // IDs of the closest words to the apple word
     *                   ),
     *                   10 => array( // IDs of the closest words to the apple word
     *                       9, 11    // IDs of the closest words to the apple word
     *                   ),
     *                   5 => array(6)
     *               ),
     *               6 => array(
     *                   9 => array(8, 10)
     *               ),
     *           ),
     *           'orange' => array(
     *               1  => array(
     *                   30 => array(29, 31)
     *               )
     *           )
     *       );
     * </code>
     *
     * @return array
     */
    public function getGraph(): array
    {
        return $this->graph;
    }
}

/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */


namespace PhpScience\TextRank\Tool;

/**
 * Class Text
 *
 * This class is for store the parsed texts.
 *
 * @package PhpScience\TextRank\Tool
 */
class Text
{
    /**
     * Multidimensional array from words of the text. Key is index of the
     * sentence, value is an array from words where key is the index of the
     * word and value is the word.
     *
     * @var array
     */
    protected $wordMatrix = [];

    /**
     * Array from sentences where key is the index and value is the sentence.
     *
     * @var array
     */
    protected $sentences = [];

    /**
     * Array from punctuations where key is the index to link to the sentence
     * and value is the punctuation.
     *
     * @var array
     */
    protected $marks = [];

    /**
     * It set the Words' matrix to the property.
     *
     * @param array $wordMatrix Multidimensional array from integer keys and
     *                          string values.
     */
    public function setWordMatrix(array $wordMatrix)
    {
        $this->wordMatrix = $wordMatrix;
    }

    /**
     * It sets the sentences.
     *
     * @param array $sentences Array's key should be an int and value should be
     *                         string.
     */
    public function setSentences(array $sentences)
    {
        $this->sentences = $sentences;
    }

    /**
     * It set the punctuations to the property.
     *
     * @param array $marks Array's key should be an int and value should be
     *                     string.
     */
    public function setMarks(array $marks)
    {
        $this->marks = $marks;
    }

    /**
     * It retrieves the words in sentence groups.
     *
     * @return array Multidimensional array from words of the text. Key is
     *               index of the sentence, value is an array from words
     *               where key is the index of the word and value is the word.
     */
    public function getWordMatrix(): array
    {
        return $this->wordMatrix;
    }

    /**
     * It retrieves the sentences.
     *
     * @return array Array from sentences where key is the index and value is
     *               the sentence.
     */
    public function getSentences(): array
    {
        return $this->sentences;
    }

    /**
     * It retrieves the punctuations.
     *
     * @return array Array from punctuations where key is the index to link to
     *               the sentence and value is the punctuation.
     */
    public function getMarks(): array
    {
        return $this->marks;
    }
}


/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */

namespace PhpScience\TextRank\Tool;

use PhpScience\TextRank\Tool\StopWords\StopWordsAbstract;

/**
 * Class Parser
 *
 * This class purpose to parse a real text to sentences and array.
 *
 * @package PhpScience\TextRank\Tool
 */
class Parser
{
    /**
     * The number of length of the smallest word. Words bellow it will be
     * ignored.
     *
     * @var int
     */
    protected $minimumWordLength = 0;

    /**
     * A single text, article, book for example.
     *
     * @var string
     */
    protected $rawText = '';

    /**
     * The array of the punctuations. The punctuation is the value. The key
     * refers to the key of its sentence.
     *
     * @var array
     */
    protected $marks = [];

    /**
     * Stop Words to ignore. These words will not be keywords.
     *
     * @var StopWordsAbstract
     */
    protected $stopWords;

    /**
     * It sets the minimum word length. Words bellow it will be ignored.
     *
     * @param int $wordLength
     */
    public function setMinimumWordLength(int $wordLength)
    {
        $this->minimumWordLength = $wordLength;
    }

    /**
     * It sets the raw text.
     *
     * @param string $rawText
     */
    public function setRawText(string $rawText)
    {
        $this->rawText = $rawText;
    }

    /**
     * Set Stop Words.
     *
     * It sets the stop words to remove them from the found keywords.
     *
     * @param StopWordsAbstract $words Stop Words to ignore. These words will
     *                                 not be keywords.
     */
    public function setStopWords(StopWordsAbstract $words)
    {
        $this->stopWords = $words;
    }

    /**
     * It retrieves the punctuations.
     *
     * @return array Array from punctuations where key is the index to link to
     *               the sentence and value is the punctuation.
     */
    public function getMarks(): array
    {
        return $this->marks;
    }

    /**
     * Parse.
     *
     * It parses the text from the property and retrieves in Text object
     * prepared to scoring and to searching.
     *
     * @return Text Parsed text prepared to scoring.
     */
    public function parse(): Text
    {
        $matrix = [];
        $sentences = $this->getSentences();

        foreach ($sentences as $sentenceIdx => $sentence) {
            $matrix[$sentenceIdx] = $this->getWords($sentence);
        }

        $text = new Text();
        $text->setSentences($sentences);
        $text->setWordMatrix($matrix);
        $text->setMarks($this->marks);

        return $text;
    }

    /**
     * Sentences.
     *
     * It retrieves the sentences in array without junk data.
     *
     * @return array Array from sentences.
     */
    protected function getSentences(): array
    {
        $sentences = $sentences = preg_split(
            '/(\n+)|(\.\s|\?\s|\!\s)(?![^\(]*\))/',
            $this->rawText,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        return array_values(
            array_filter(
                array_map(
                    [$this, 'cleanSentence'],
                    $sentences
                )
            )
        );
    }

    /**
     * Possible Keywords.
     *
     * It retrieves an array of possible keywords without junk characters,
     * spaces and stop words.
     *
     * @param string $subText It should be a sentence.
     *
     * @return array The array of the possible keywords.
     */
    protected function getWords(string $subText): array
    {
        $words = preg_split(
            '/(?:(^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
            $subText,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $words = array_values(
            array_filter(
                array_map(
                    [$this, 'cleanWord'],
                    $words
                )
            )
        );

        if ($this->stopWords) {
            return array_filter($words, function($word) {
                return !ctype_punct($word)
                        && strlen($word) > $this->minimumWordLength
                        && !$this->stopWords->exist($word);
            });
        } else {
            return array_filter($words, function($word) {
                return !ctype_punct($word)
                        && strlen($word) > $this->minimumWordLength;
            });
        }
    }

    /**
     * Clean Sentence.
     *
     * It clean the sentence. If it is a punctuation it will be stored in the
     * property $marks.
     *
     * @param string $sentence A sentence as a string.
     *
     * @return string It is empty string when it's punctuation. Otherwise it's
     *                the trimmed sentence itself.
     */
    protected function cleanSentence(string $sentence): string
    {
        if (strlen(trim($sentence)) == 1) {
            $this->marks[] = trim($sentence);
            return '';

        } else {
            return trim($sentence);
        }
    }

    /**
     * Clean Word.
     *
     * It removes the junk spaces from the word and retrieves it.
     *
     * @param string $word
     *
     * @return string Cleaned word.
     */
    protected function cleanWord(string $word): string
    {
        return mb_strtolower(trim($word));
    }
}


/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */



namespace PhpScience\TextRank\Tool\StopWords;

/**
 * Class StopWordsAbstract
 *
 * @package PhpScience\TextRank\Tool\StopWords
 */
abstract class StopWordsAbstract
{
    /**
     * Stop words for avoid dummy keywords.
     *
     * @var array
     */
    protected $words = [];

    /**
     * It retrieves the word exists or does not in the list of Stop words.
     *
     * @param string $word
     *
     * @return bool It is True when it exists.
     */
    public function exist(string $word): bool
    {
        return array_search($word, $this->words) !== false;
    }
}

/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */



namespace PhpScience\TextRank\Tool\StopWords;

/**
 * Class English
 *
 * @package PhpScience\TextRank\Tool\StopWords
 */
class English extends StopWordsAbstract
{
    /**
     * Stop words for avoid dummy keywords for Language English.
     *
     * @var array
     */
    protected $words = [
        'a',
        'about',
        'above',
        'above',
        'across',
        'after',
        'afterwards',
        'again',
        'against',
        'all',
        'almost',
        'alone',
        'along',
        'already',
        'also',
        'although',
        'always',
        'am',
        'among',
        'amongst',
        'amoungst',
        'amount',
        'an',
        'and',
        'another',
        'any',
        'anyhow',
        'anyone',
        'anything',
        'anyway',
        'anywhere',
        'are',
        'around',
        'as',
        'at',
        'back',
        'be',
        'became',
        'because',
        'become',
        'becomes',
        'becoming',
        'been',
        'before',
        'beforehand',
        'behind',
        'being',
        'below',
        'beside',
        'besides',
        'between',
        'beyond',
        'bill',
        'both',
        'bottom',
        'but',
        'by',
        'call',
        'can',
        'cannot',
        'cant',
        'co',
        'con',
        'could',
        'couldnt',
        'cry',
        'de',
        'describe',
        'detail',
        'do',
        'done',
        'down',
        'due',
        'during',
        'each',
        'eg',
        'eight',
        'either',
        'eleven',
        'else',
        'elsewhere',
        'empty',
        'enough',
        'etc',
        'even',
        'ever',
        'every',
        'everyone',
        'everything',
        'everywhere',
        'except',
        'few',
        'fifteen',
        'fify',
        'fill',
        'find',
        'fire',
        'first',
        'five',
        'for',
        'former',
        'formerly',
        'forty',
        'found',
        'four',
        'from',
        'front',
        'full',
        'further',
        'get',
        'give',
        'go',
        'had',
        'has',
        'hasnt',
        'have',
        'he',
        'hence',
        'her',
        'here',
        'hereafter',
        'hereby',
        'herein',
        'hereupon',
        'hers',
        'herself',
        'him',
        'himself',
        'his',
        'how',
        'however',
        'hundred',
        'ie',
        'if',
        'in',
        'inc',
        'indeed',
        'interest',
        'into',
        'is',
        'it',
        'its',
        'itself',
        'keep',
        'last',
        'latter',
        'latterly',
        'least',
        'less',
        'ltd',
        'made',
        'many',
        'may',
        'me',
        'meanwhile',
        'might',
        'mill',
        'mine',
        'more',
        'moreover',
        'most',
        'mostly',
        'move',
        'much',
        'must',
        'my',
        'myself',
        'name',
        'namely',
        'neither',
        'never',
        'nevertheless',
        'next',
        'nine',
        'no',
        'nobody',
        'none',
        'noone',
        'nor',
        'not',
        'nothing',
        'now',
        'nowhere',
        'of',
        'off',
        'often',
        'on',
        'once',
        'one',
        'only',
        'onto',
        'or',
        'other',
        'others',
        'otherwise',
        'our',
        'ours',
        'ourselves',
        'out',
        'over',
        'own',
        'part',
        'per',
        'perhaps',
        'please',
        'put',
        'rather',
        're',
        'same',
        'see',
        'seem',
        'seemed',
        'seeming',
        'seems',
        'serious',
        'several',
        'she',
        'should',
        'show',
        'side',
        'since',
        'sincere',
        'six',
        'sixty',
        'so',
        'some',
        'somehow',
        'someone',
        'something',
        'sometime',
        'sometimes',
        'somewhere',
        'still',
        'such',
        'system',
        'take',
        'ten',
        'than',
        'that',
        'the',
        'their',
        'them',
        'themselves',
        'then',
        'thence',
        'there',
        'thereafter',
        'thereby',
        'therefore',
        'therein',
        'thereupon',
        'these',
        'they',
        'thickv',
        'thin',
        'third',
        'this',
        'those',
        'though',
        'three',
        'through',
        'throughout',
        'thru',
        'thus',
        'to',
        'together',
        'too',
        'top',
        'toward',
        'towards',
        'twelve',
        'twenty',
        'two',
        'un',
        'under',
        'until',
        'up',
        'upon',
        'us',
        'very',
        'via',
        'was',
        'we',
        'well',
        'were',
        'what',
        'whatever',
        'when',
        'whence',
        'whenever',
        'where',
        'whereafter',
        'whereas',
        'whereby',
        'wherein',
        'whereupon',
        'wherever',
        'whether',
        'which',
        'while',
        'whither',
        'who',
        'whoever',
        'whole',
        'whom',
        'whose',
        'why',
        'will',
        'with',
        'within',
        'without',
        'would',
        'yet',
        'you',
        'your',
        'yours',
        'yourself',
        'yourselves'
    ];
}


/**
 * PHP Science TextRank (http://php.science/)
 *
 * @see     https://github.com/doveid/php-science-textrank
 * @license https://opensource.org/licenses/MIT the MIT License
 * @author  David Belicza <87.bdavid@gmail.com>
 */



namespace PhpScience\TextRank;

use PhpScience\TextRank\Tool\Graph;
use PhpScience\TextRank\Tool\Parser;
use PhpScience\TextRank\Tool\Score;
use PhpScience\TextRank\Tool\StopWords\StopWordsAbstract;
use PhpScience\TextRank\Tool\Summarize;

/**
 * Class TextRankFacade
 *
 * This Facade class is capable to find the keywords in a raw text, weigh them
 * and retrieve the most important sentences from the whole text. It is an
 * implementation of the TextRank algorithm.
 *
 * <code>
 *      $stopWords = new English();
 *
 *      $textRank = new TextRankFacade();
 *      $textRank->setStopWords($stopWords);
 *
 *      $sentences = $textRank->summarizeTextFreely(
 *          $rawText,
 *          5,
 *          2,
 *          Summarize::GET_ALL_IMPORTANT
 *      );
 * </code>
 *
 * @package PhpScience\TextRank
 */
class TextRankFacade
{
    /**
     * Stop Words
     *
     * Stop Words to ignore because of dummy words. These words will not be Key
     * Words. A, like, no yes, one, two, I, you for example.
     *
     * @see \PhpScience\TextRank\Tool\StopWords\English
     *
     * @var StopWordsAbstract
     */
    protected $stopWords;

    /**
     * Set Stop Words.
     *
     * @param StopWordsAbstract $stopWords Stop Words to ignore because of
     *                                     dummy words.
     */
    public function setStopWords(StopWordsAbstract $stopWords)
    {
        $this->stopWords = $stopWords;
    }

    /**
     * Only Keywords
     *
     * It retrieves the possible keywords with their scores from a text.
     *
     * @param string $rawText A single raw text.
     *
     * @return array Array from Keywords. Key is the parsed word, value is the
     *               word score.
     */
    public function getOnlyKeyWords(string $rawText): array
    {
        $parser = new Parser();
        $parser->setMinimumWordLength(3);
        $parser->setRawText($rawText);

        if ($this->stopWords) {
            $parser->setStopWords($this->stopWords);
        }

        $text = $parser->parse();

        $graph = new Graph();
        $graph->createGraph($text);

        $score = new Score();

        return $score->calculate(
            $graph, $text
        );
    }

    /**
     * Highlighted Texts
     *
     * It finds the most important sentences from a text by the most important
     * keywords and these keywords also found by automatically. It retrieves
     * the most important sentences what are 20 percent of the full text.
     *
     * @param string $rawText A single raw text.
     *
     * @return array An array from sentences.
     */
    public function getHighlights(string $rawText): array
    {
        $parser = new Parser();
        $parser->setMinimumWordLength(3);
        $parser->setRawText($rawText);

        if ($this->stopWords) {
            $parser->setStopWords($this->stopWords);
        }

        $text = $parser->parse();
        $maximumSentences = (int) (count($text->getSentences()) * 0.2);

        $graph = new Graph();
        $graph->createGraph($text);

        $score = new Score();
        $scores = $score->calculate($graph, $text);

        $summarize = new Summarize();

        return $summarize->getSummarize(
            $scores,
            $graph,
            $text,
            12,
            $maximumSentences,
            Summarize::GET_ALL_IMPORTANT
        );
    }

    /**
     * Compounds a Summarized Text
     *
     * It finds the three most important sentences from a text by the most
     * important keywords and these keywords also found by automatically. It
     * retrieves these important sentences.
     *
     * @param string $rawText A single raw text.
     *
     * @return array An array from sentences.
     */
    public function summarizeTextCompound(string $rawText): array
    {
        $parser = new Parser();
        $parser->setMinimumWordLength(3);
        $parser->setRawText($rawText);

        if ($this->stopWords) {
            $parser->setStopWords($this->stopWords);
        }

        $text = $parser->parse();

        $graph = new Graph();
        $graph->createGraph($text);

        $score = new Score();
        $scores = $score->calculate($graph, $text);

        $summarize = new Summarize();

        return $summarize->getSummarize(
            $scores,
            $graph,
            $text,
            10,
            3,
            Summarize::GET_ALL_IMPORTANT
        );
    }

    /**
     * Summarized Text
     *
     * It finds the most important sentence from a text by the most important
     * keywords and these keywords also found by automatically. It retrieves
     * the most important sentence and its following sentences.
     *
     * @param string $rawText A single raw text.
     *
     * @return array An array from sentences.
     */
    public function summarizeTextBasic(string $rawText): array
    {
        $parser = new Parser();
        $parser->setMinimumWordLength(3);
        $parser->setRawText($rawText);

        if ($this->stopWords) {
            $parser->setStopWords($this->stopWords);
        }

        $text = $parser->parse();

        $graph = new Graph();
        $graph->createGraph($text);

        $score = new Score();
        $scores = $score->calculate($graph, $text);

        $summarize = new Summarize();

        return $summarize->getSummarize(
            $scores,
            $graph,
            $text,
            10,
            3,
            Summarize::GET_FIRST_IMPORTANT_AND_FOLLOWINGS
        );
    }

    /**
     * Freely Summarized Text.
     *
     * It retrieves the most important sentences from a text by the most important
     * keywords and these keywords also found by automatically.
     *
     * @param string $rawText           A single raw text.
     * @param int    $analyzedKeyWords  Maximum number of the most important
     *                                  Key Words to analyze the text.
     * @param int    $expectedSentences How many sentence should be retrieved.
     * @param int    $summarizeType     Highlights from the text or a part of
     *                                  the text.
     *
     * @return array An array from sentences.
     */
    public function summarizeTextFreely(
        string $rawText,
        int $analyzedKeyWords,
        int $expectedSentences,
        int $summarizeType
    ): array {
        $parser = new Parser();
        $parser->setMinimumWordLength(3);
        $parser->setRawText($rawText);

        if ($this->stopWords) {
            $parser->setStopWords($this->stopWords);
        }

        $text = $parser->parse();

        $graph = new Graph();
        $graph->createGraph($text);

        $score = new Score();
        $scores = $score->calculate($graph, $text);

        $summarize = new Summarize();

        return $summarize->getSummarize(
            $scores,
            $graph,
            $text,
            $analyzedKeyWords,
            $expectedSentences,
            $summarizeType
        );
    }
}

use PhpScience\TextRank\Tool\StopWords\English;

// String contains a long text, see the /res/sample1.txt file.
$text = "Can you imagine if a republican was in the WH when this happened?  They glorified raygun who was a terrible POTUS.  We can only imagine thank goodness.'
We would be hearing non stop counterfactuals about how the GOP president turned the economy around after devastating Obama years no doubt. '
People hear what they want to hear see what they want to see and they will continue to push their agenda regardless of the facts. It doesn't matter how much fact checking goes on. Obama opponents and haters will still find a reason to dislike him and thi...
Hatred of Obama stems from a lot of different emotions and cannot be countered with reality. '
He should run the tape back on all the things he railed against Bush on that he is actually doing himself.   All hail the most transparent administration! '
Reality check:  The stock market has nothing to do with how the economy and wages are performing.  See the Fed QE program since 2009.  This is propped up the markets since the money is essentially being pumped into it.  Thus the markets are on sugar high...
Yes because when the stock market is soaring during a republican administration the people who are cheering it on and acting like its an indicator if a roaring economy are all liberal democratic base voters. Oh wait no they're not. Either side will trump...
Tell us more about how the economy hasn't improved since 2008.
Hey cool a non-sequitur. '
If given a choice between admitting that things have gotten better during the Obama administration and denying that reality Republicans will choose the latter.The streets could be paved with gold and rainbows could be shooting out of Janet Yellin's ass a...
I think you and I have differing definitions of respected analysts. '
Look at U6 labor participation wages record number on food assistance etc...It's also easy to improve coming from nothing but here's the catch:  It's improved DESPITE Obama and his jobs summits his ACA law his tax increases and other Kensyian methods.  N...
Exactly. No the stock market is not a great barometer of the overall economy but things like unemployment rate wage growth bankruptcies foreclosures and inflation are and those things are markedly improved as well. I think if OP were actually responding ...
You know what else is a non sequitur? ABORTION! Obama killing babies amirite? (See what I did is I used words similar to your words and then transitioned into an entirely different topic making the non sequitur difficult to spot for most people.) '
I feel like cutting the corporate tax rate would not bring any money at all back to our economy but instead just increase the amount of money sent off shore. '
&gt; Look at U6[Okay.](http://portalseven.com/employment/unemployment_rate_u6.jsp)&gt;It's improved DESPITE Obama and his jobs summits his ACA law his tax increases and other Kensyian methods.Meanwhile:&gt;[Recently each of these eminent economists was a...
Except this wouldn't happen under a GOP presidency. They're only good at running up the deficit and crashing the economy. 
The stock market is certainly an indicator of how the economy is doing.  It is not the only one by any stretch of the imagination but it is important.All the other economic indicators are up as well.  Unemployment is half what it was.  GDP has been growi...
http://politicalticker.blogs.cnn.com/2012/05/23/romney-promises-to-bring-unemployment-down-to-6/That has to hurt. I can see why you're upset.(current US rate: 5.5%)
The lack of transparency is a good point but lets be honest here and acknowledge that it was a little naive to think that was ever going to happen.  It would have been nice but no administration will ever reveal more than they absolutely have.  As far as...
Republicans care more about hating someone for the color of their skin than the country that elects them to Govern.  I feel like we need another civil rights movement that will rid us of Republicans and Conservatives.  They are truly some misguided unedu...
[deleted]'
&gt; Part of this has to do with the Fed pumping money into the markets and keeping interest rates low. Both the federal government and the central bank took steps to halt the collapse of the economy as they should do when such a massive recession hits. ...
It's Keynesian. Cutting a check to everyone would also qualify as a Keynesian policy by the way.Sure the stock market isn't the end all be all of economic indicators but it *is* an indicator. Stock prices have risen because investors think that it is a g...
If you truly think the current valuations of the markets are based on sound fundamental financial principles you're listening to the wrong analysts.
&gt;  I haven't heard an actual person say that Obamacare is doing anything good for them  Then you haven't been paying much attention - or you've paid attention to the wrong news sources who refuse to say anything good about it. The uninsured rate is do...
[deleted]'
&gt; rainbows could be shooting out of Janet Yellin's assTo be fair I'm not sure how this helps the average American.Also streets paved with gold? Exactly the kind of government waste I knew we would get under a Democratic President!/s
&gt; I haven't heard an actual person say that Obamacare is doing anything good for them Actual person checking in.  It helped quite a bit.
There *are* things to be criticizing Obama about.The problem is that most of the things the Republicans have been railing against haven't been problems but successes in many cases.  Most of the persistent economic problems (lower wages and such) have to ...
Yeah like those morons who hate Obama for executing a war in violation of the war powers act for enshrining indefinite detention into law for promising openness and transparency and then prosecuting a record number of leakers and whistleblowers for denyi...
Exactly! I don't think Obama was perfect but almost everything I dislike (spying transparency government expansion etc) are heavily supported by the GOP. They are things we will absolutely see more of if the GOP takes over. The reason they don't bitch ab...
Ah yes: the most reliable sources of information are comment threads on Reddit. That's some empirical analysis right there. '
None of those things are the economy and therefore they don't matter.
If the people in Congress who oppose Obama focused on that stuff then they might have a leg to stand on.  But instead they focus on Benghazi his executive action and Obamacare which are all losing battles.'
[deleted]'
I'm ok with people hating on Obama for real reasons. It's important to be critical of our leaders. I just can not stand how Fox News and the Republican party create imaginary reasons and claim that Obama is a dictator. You lose all creditability in my mi...
[Let's run the clock back on this one too](http://www.huffingtonpost.com/2012/10/22/obama-romney-russia_n_2003927.html).That's one zinger I bet Obama wishes he could take back.
This propaganda is so thick. Are people really buying this professional wrestling act? They are robbing us blind as a team. The leaders of both parties are total globalist scumbags bought and sold a long time ago and MSNBC is owned by GE which received s...
[deleted]'
[deleted]'
I shattered my elbow. Without obamacare I would have had no insurance and I would be bankrupt 4 months after getting married.If you haven't heard of people helped by obamacare you aren't looking very hard.
Because reddit either didn't want it or they wanted single payer. Reddit is a community of extremists and wont be satisfied. That's why everyone shit talks it. No one is interested in the people that it's helping they only care about two things: 1. Am I ...
That can be a general statement for human nature. Sad but true see it everywhere in business everyday.'
The country would be too distracted with a war with Iran.'
My crazy conservative friends were saying five years ago that we are absolutely going to be hit with a second double dip recession and inflation was going to go through the roof.  Because of Barry and his socialism. They never admit they were wrong and j...
&gt;  Every respected analyst is seeing a major correction sometime sooner than later.You mean the geniuses who predicted the last economic meltdown? There is no such thing as a respected analyst. That said predicting a correction is like predicting a bo...
Apparently he doesn't know the stock market doesn't have much to do with the general economy anymore.But nice try.How many of you non stockholders are richer?
I would like to remind him about his claims of a transparent government if still elected. They ALL bluster and lie.'
Most of the people that actually hate Obama for the reasons you gave are liberals. We were talking about right wingers. You're interrupting the circle jerk!
I do agree with some of your points but this:&gt;for enshrining indefinite detention into lawwas entirely the fault of the Republicans in Congress assuming you're talking about the NDAA.For those who don't know the background here the NDAA is the bill th...
Right everyone's blameless for everything except the ones you don't like.
It almost got me a kidney transplant. In fact I only got on the transplant list because of the ACA. If I had been on the list a few years earlier before my health made it too difficult I would have had a kidney transplant. So for about a year there the A...
The markets have been glutonous since QE began.  Google Kyle Bass.'
True and particularly true about the non-stockholders.  However by all measures the economy has greatly improved.'
Refueled no more election no more fucks to give Obama is the best Obama'
Over the first five years of Obamaxe2x80x99s presidency the U.S. economy grew more slowly than during any five-year period since just after the end of World War II averaging less than 1.3 percent per year. If we leave out the sharp recession of 1945-46 f...
I don't want anything to do with anything that comes shooting out of Janet Yellin's ass. I would complain too. And I'm certainly not conservative. 
That's weird because I have seen a lot of positive anecdotal evidence on Reddit threads. It's almost as if personal bias affects our experience of Reddit.
&gt; You're honestly the third person I've heard say this since it's inception. Maybe you should get out more?Of course I don't expect you to personally talk to the millions of people who now have health insurance the millions of parents who were able to...
To be fair if I was president and had the FED print out 80 billion dollars a month to just give to wallstreet the stockmarket would go up. Just because the rich are getting richer doesn't change the fact that class inequality continues to grow.And becaus...
And starting wars. Not so good at waging or winning the wars. But really really good at starting them!'
[deleted]'
My parents recently blamed Obama for lower wages by Americans and that Wall Street was seeing most of the gains.Problem is this is a Republican policy (e.g. trickle down).  And yet they're adamant against having a rise in the minimum wage or in controlli...
Love of Obama stems from a lot of different emotions and cannot be countered with reality.'
One of those insiders he appointed was for the FCC. The FCC that just gave us net neutrality and stopped ISPs from ruining our internet in a 3-2 vote with that appointee being the deciding factor.  People like you disgust me. You live in a state of fear ...
&gt;  That's kind of like looking at the exterior of a car and being able to determine the engine is running just fine.Terrible analogy. Looking at the exterior of a car is probably the best indicator of how well the engine runs. If the exterior is immac...
That's because they agree with the other things.  They love it when Obama acts like a conservative Democrat they just don't like being reminded that he is a conservative Democrat.  
[deleted]'
All the points above are republican platforms.'
And is a hell of a lot harder to find than hate particularly among Liberals who once supported him. '
I agree with the first part not with the second.'
Congress is a clusterf*ck and has left the Fed flying solo. I consider monetary and fiscal policy to be individual oars on a boat. If both people rowing the boat work together the boat moves along very well. If both people rowing are working against one ...
I agree that he is far too conservative.'
Congress is a clusterf*ck leaving the Fed flying solo. I consider monetary and fiscal policy to be individual oars on a boat. If both people rowing the boat work together the boat moves along very well. If both people rowing are working against one anoth...
The key phrase in there since the Great Depression. You may have forgotten but we came awfully close to another one. So yes after coming dangerously close to another Depression we would not have a growth that matches the longest bull market in world hist...
All owned by the Big banking families that privately produce and distribute our currency. 'Federal reserve' is nothing but a cover up name for a private business that prints money with no backing
In general emotions are hard to counter with reality. So well done!  However those who like Obama (not love) can point to a very nice list of accomplishments and even acknowledge his failings. The issue for the haters is that his failings are not greater...
&gt;  for continuing warrantless spying on AmericansGot a source to provide some proof of that? It is widely known that warrantless wiretaps occurred under Bush. But my understanding of the available information is that Obama's domestic spying falls with...
The slow growth is a result of not doing enough to stimulate the economy. If anything the stimulus should have been closer to 3 Trillion dollars to exceed the amount lost from the economy as a result of the crash in 2008Are you familiar with neo-Keynesia...
It's because both parties want to keep a majority distracted with issues that don't matter. Hegelian Dialectic.
Mostly racism though.'
Right but I wasn't talking about Obama's most moronic critics I was talking about Obama's most thoughtful critics./r/politics seems to really dislike talking about thoughtful criticism of Obama and focus instead on idiots and pretend that all of Obama's ...
[Here's some reality I bet Obama wishes he could take back](http://www.huffingtonpost.com/2012/10/22/obama-romney-russia_n_2003927.html).
His underlying point was that while there are valid criticisms of the president republicans don't make those critiques because they support those activities.
You gonna blame Republicans in Congress for an executive order?https://www.aclu.org/national-security/president-obama-issues-executive-order-institutionalizing-indefinite-detention'
How is the FCC banking governance?'
Obviously paving the roads with gold is a waste we won't have roads after a month with all those illegals stealing the roads to take back to their country/s 
Thanks Republicans!'
my employer says they only offer health insurance because of mandates in Obamacare. I have insurance. Thanks Obama! '
See you see this as being a bad thing but I look at it as a testament to how much Obama has pushed to strengthen the economy while Congress did everything in it's power to hold it back. The Great Recession was nearly as bad as the Great Depression I don'...
The president controls if people get rich?'
[deleted]'
I think he's been a good president based on the bar that's been set. I'm still upset about the lack of transparency though. When I cast my vote I was voting for his transparency platform. 
No need for the /s.  If the streets were actually paved with gold I would be pretty pissed off.'
https://www.aclu.org/national-security/fix-fisa-end-warrantless-wiretappingThat plus national security letters used to secretly spy on people without any warrant.'
Well one of the realities and perhaps not the only one... but interest rates being at an all time low is forcing people to put their money into the stock market as it's the only place where you can currently make money.  I fully expect the market to come...
The weird thing is that all those things you mention are supported by the republicans that hate him.  So must be some other reason they hate him then eh?'
&gt; Over the first five years of Obamaxe2x80x99s presidency the U.S. economy......was recovering from a near complete collapse that started before he came into office and while being burdened with the most obstructionist Congress in U.S. history.You sou...
I am doing pretty good actually and the area I live in (Buffalo NY) has been going through a huge economic boom over the past few years. That's pretty good for an area that was left for dead after the heavy industry started leaving 40 years ago.Edit: Dow...
Republicans and Obama agree more often and pass more legislation together than /r/politics is comfortable admitting.'
Some fair points. But just because Obama is a better president than Bush and others from the past does not make him a good president. '
I don't understand why we accept tattling on someone else for something as a defense for screwing up.   This is how kids work. Barack why didn't you finish your homework? His response?   Well Steve cheated on his assignmentThis causes the retards to all ...
Wait wut?  Russia is our biggest threat?   '
Even worse than Dec 31 2008 -0.92%? Wow Obama is so terrible his positive growth is worse than GW's negative growth. Fucking right wingers...
Are we pretending republicans who hate him are the only people who hate him?'
The global situation still needs to change a lot more before you can consider Romney right about that one.'
But the President doesn't control the fed so...... 
I would still take Obama over Romney any day. Oh man could you imagine how Mitt Romney would of handled foreign policy towards Russia the past couple of years? It makes me cringe.'
&gt; Anyone taking this seriously anymore is an idiot.You are talking about what you said right?'
We ARE going to have another crash.  But it absolutely is going to be because of the lack of banking oversight and the weakening of Dodd-Frank.  '
Problem is is that many of those respected analysts have been predicting a stock market downturn and runaway inflation for a while. It's just sad now. '
No but this thread is about GOP critics.  '
If only these were the things the conservative media happened to be stressing they would have a much more defensible position. As it stands their moneyed interests *like* these things so they can't really criticize them so harshly. So we get the endless ...
&gt;It's also easy to improve coming from nothingYou mean the nothing that Bush's/republican's policies have driven us to?
I'm not talking about conservatives and I don't know why we always conflate people who dislike Obama with conservatives.There are very very very legitimate reasons not to like Obama.'
This one goes both ways.'
What's sad about this whole ordeal is that Obama has spent his entire presidency on an endless campaign where everything is us vs them. He's not up for re-election anymore... Why the need to still keep throwing jabs at the GOP? Specially when they contro...
Without a doubt.'
Typically when both parties agree on something it is not in our best interest'
[Legislation signed by the President](https://www.whitehouse.gov/briefing-room/signed-legislation)    Care to expand on which legislation it is that you object to.    The 'No Social Security for Nazi's Act' perhaps?
You call  saying These guys were wrong about all these things tattling?  Because I thought tattling was telling someone that another someone said or did something they weren't supposed to know about.  He's not tattling he's saying their full of sh*t.'
The people I was responding to were commenting simply about people who hate Obama and were specifically talking about there being all sorts of reasons people hated Obama and implying none were rational.'
99% of the people who hate Obama will not cite any of the very valid reasons you just listed.  As such the previous comment is still valid.'
The fed does not take commands from the president'
The previous comment was always valid.People who hate Obama for the reasons I listed cannot be countered with reality . . . because reality is the reason they hate Obama.You think the family of that teenage boy who was born in Denver and got drone-bombed...
[deleted]'
Let's see some playbacks of his end the war lies'
Well if you're  a non-stockholder that probably means you don't have a 401K IRA or retirement plan.   Which means that you're not making good financial decisions.    So we wouldn't expect you to be richer.
You are cherry picking facts to align with your narrative. You completely leave out that we were in the worst recession since the great depression but for some reason this has been completely been glossed over.'
I think your point is so spot on. Too bad it's getting ignored. I think Obama has been pretty good but everything I don't like about him is something the Republicans do as well.
Hey hey hey....this is a GOP-Dem fight. Leave all that open minded critical analysis at home! /s'
Not surprisingly Obama did not ask anyone to run the tape back on his ACA promises (if you like your plan you can keep it) or his promise to me the most transparent administration ever. Or his previous statements about abuse of executive actions or his p...
I guess we could go look at how well Kansas or Wisconsin are doing following roughly your suggestions.   Or we could go live in your fantasy world and pet our unicorns.'
My uncle is alive because of obamacare. People can legally visit Cuba for the first time in decades.We're actually having discourse with Iran that doesn't  involve the words I'm gonna blow you up'
Perhaps you remember the firewall of 'filibuster everything the democrats/Obama does' of his first four years differently than I do.
Have I objected to any legislation?I'm just saying that Obama has voted for more legislation that Republicans supported than /r/politics likes to admit.
. . . except for prosecuting a war in violation of the War Powers Act.  Republicans were pissed about that.'
Fox News Analyst here..... I'm rubber you're glue.... Pitchforks and torches at 11.
My son has ore existing conditions that would have bankrupted me if he had been born before the ACA and would have caused him immeasurable difficulty getting insured when he grew up without the ACA. No the plan is not perfect and I would greatly prefer a...
Why?  I mean where else could you get drunk and piss on gold?'
Las Vegas?'
Even if gold was a very abundant and cheap material it would still not be very good for road paving.  First off it would be super slippery when it rained.  Secondly it would rut like crazy.  We're better off with roads how they are.
Because a lot of the thoughtful criticism ignores the fact that Obama is the most progressive president since Carter. I'm certainly unhappy with some aspects of Obama's presidency but he's been a breath of fresh air compared to many of his predecessors. ...
Looking at some of these comments it's like a lot of people are mad that the economy has improved.
Good old Nixonrichard. Won't make an argument so you can't prove him wrong.
tell me what GOP represents today and give me one reason to reach compromise'
It wont happen. What the 60s did was to help make it not okay to be a racist.  So they went into the closet ironically.Now we are seeing these closet racists poke their head out from their elected positions and saying subversive things to cause turmoil a...
Not when it was them doing it.'
I'm starting to think the whole stock marked closed above blah blah blah is a pretty shitty indicator of how the economy appears to your average American. Dubya cited the same BS all the time and every day seemed worse than the last when he was at the he...
so.... why does he get the credit for the stock market? '
Exactly.'
But they didn't stop so therefore it's not Obama's fault.Until we have a Republican president at which point it remains the Democrats fault for not stopping it.
Lol... Says the stereotyping misguided uneducated person.'
[deleted]'
It's an easy straw man for this sub. They pretend anyone critical of the Dems or Obama specifically is a far-right Republican racist just like the Republicans pretend all criticism of them is from gay homosexual commies.
Can we not pull this they're ALL alike shit? It's never about common ground it's always about placing blame.'
He's done great for wall street.So so for average Americans.Good on foreign policy.Pro science.Terrible for civil liberties. 
The stock market is a result of money creation by the Fed. Most Americans are not enjoying the benefits of this which is why income inequality keeps increasing. It's easy to make money when you can borrow at 0% from the Fed. Only problem is that they are...
The trouble is that the GOP can spit lies a lot faster than the Democrats can rebut them'
I'd prefer candidate Obama
&gt; than the country that elects them to Govern. Oh thank goodness.  Republicans keep telling us he's a communist dictator I almost forgot that we voted for the guy.
Great job President Obama!!!! Your the best President we have had in decades!!!I voted for you twice and I feel proud to say so.'
Because every real economist knows about the strong correlation between the economy and an index's performance....
Can you help me out and show me what in that article is proof? I followed some of the links and it seems to talk about things like Americans being inadvertently caught up in international surveillance. I'm looking for something that supports your stateme...
Politicians certainly are fickle.  '
Imagine if he'd had *any* type of governance cooperation from the GOP.You know like it's supposed to be. Not just pure obstruction.
Look at the people trying so hard to deflect your comment. Funny as fuck.'
Maybe he shouldn't have gone on an ignore everything the republicans say streak. But that's not really the point... A big part of his job is to reach compromise. In the US the President is voted in by barely above 50% of voters but he represents the full...
Not quite sure what you're trying to say as it seems contradictory. But my understanding is they did stop the illegal part of it that was taking place under Bush.
Not to mention too malleable to be used practically.'
lol wut? really? so after 6 years of the GOP calling him a fraud a kenyon a muslim a gay guy with a tranny wife and gangster daughters. From day one having a meeting and deciding that the #1 priority is to get him out of office 6 years of never compromis...
Well the point was to be contradictory haha.My point was he's using things Obama hasn't stopped yet as things he did wrong when it's more apt to blame the person who started it.
The first link in the article I provided has good bullet points to help:https://www.aclu.org/nsa-surveillance-procedures&gt; I'm looking for something that supports your statement which makes it sound like it is widespread and specifically targeting Amer...
&gt; Why the need to still keep throwing jabs at the GOP? If someone keeps punching you in the face while simultaneously trying to kick your legs out from under you extending your hand in friendship will only earn you a more severe beating.It is worth re...
Because GOP critics are the first to give him the blame. '
because the sanctions on Russia have been entirely ineffective and their doing great over there...Sactions crippled Russia's economy not exactly USSR level of power that Romney was alluding to.
I think its odd how you libs are always complaining about the tea party blocking everything Obama wants to do then when things turn out to have gotten better instead of worse you want to give Obama the credit for most of his shit getting blocked by a Rep...
And when the stocks took a huge dump he was the guy who said not to judge the recovery based on where the market was. Hmm. '
&gt;  I feel like we need another civil rights movement that will rid us of Republicans and Conservatives. Surburban Warriors. Very interesting read about the Conservative movement we see today. What we know of the Conservative party and the Republican p...
It's not just that. Many of these people also spread racism to their children and now those people go on the Internet and continue their positions further than their parents were willing to. That's why we have things like Stormfront 8chan and the racist ...
Not that I agree with you neccasarily but you left out the escalation of drone use. The thing is if there was a legit opposition  these are debates that would be happening internally in the Democratic Party or externally on the floors of congress. Howeve...
I don't think he's going to be offering that advice very much.
ITT: /r/Politics rushing to deflect and defend their savior from people with common sense. Looks like the propaganda didn't work today.
It's more about moving the goalposts.  They are doing that constantly.
not to mention every index that people refer to when referencing the stock market are changed to include the largest companies from the largest sectors within the economy.  If those companies are doing poorly it's probably a reflection on that sector hav...
Gotcha. Thanks.'
the house could be on fire and these fucking rubes would just claim it is how they heat the house to stay warm. '
Except that's not what any of his political critics chastise him for. You're listing valid reasons to criticize him (though honestly if you think his opponents from the last two elections would be any better than him on these issues I'm very curious why)...
Executive orders home slice.'
&gt;Except that's not what any of his political critics chastise him for.Yes it is.  I know we don't like to talk about it here on /r/politics but the things I listed are things the ACLU and EFF have been criticizing Obama for for years.
Ah [confirmation bias](http://en.wikipedia.org/wiki/Confirmation_bias) via [projection](http://en.wikipedia.org/wiki/Psychological_projection).  Nice combo.'
[deleted]'
&gt;Not that I agree with you neccasarily but you left out the escalation of drone use.I mention the expansion of war on Muslim countries which is implicitly about drone use.'
So Obama was responsible for 30 company's stock price? He's amazing!
This has nothing to do with the article but I hate when people refer to the Dow Jones Industrial as the stock market. It's actually a pretty useless indicator of market strength. It's about time people catch on to the fact that the S&amp;P 500 is a much ...
He was widely criticized for being an industry insider. You are just proving my point by the fact that the only argument you could conjure up from your Obama Hate-bag was that pitiful attempt. '
AND he's a baby step to the left yet we're bombarded with people who think he's Chairman Fucking Mao.
[deleted]'
Gold doesn't rust hombre.
In context that was the way I took it. Maybe I read too much into it though. And the first article you linked to is one of the ones I had looked at. It's the one talking about Americans getting caught up in international surveillance. It does not seem to...
[deleted]'
This Obama has a lot more credibility.'
the war actions i can agree with.but isn't it true that for the other reasons you just cited those were done by sub sections of the government that he cannot have actual involvement in and can only fire their head?
Haha yeah like obamacare. Wait no they fought tooth and nail on that one and still are. I'll wait for an explanation on how the letter to Iran was a good thing even though it embarrassed you internationally. 
No they were specifically talking about the people who hate Obama who *aren't* rational. And I don't think even you can disagree that in all likelihood most of the people who hate Obama and who voted against him are not the rational thinkers you and othe...
Can confirm. Source: I live in Vegas and have peed on golden things.'
[deleted]'
This was also me from 5 hours ago providing some more context:&gt;And yes hiring has really picked up. Wages havent and I think both wages and hiring would go up if some businesses didn't have so much idle wealth; pies aren't static in size but they are ...
Are you talking about *actual* hatred or the sort of hating that haters do?Actual hatred like actual love is entirely emotional anyway so that's completely uninteresting; it must be the other sort of hatred -- the sort that means criticism must be what y...
[deleted]'
I make twice what I did when Obama took office.'
I don't think that was a typo. I think he meant that it would form ruts due to being such a malleable metal and be painful to drive on.
Sorry I should have been more clear. When I said political critics I meant the politicians in Congress that he has to deal with on a regular basis to try to get things done. Obviously human/civil rights groups such as the ACLU criticize him for those thi...
That ONE'
Huh maybe you're right I've just never really seen rut used as a verb.
Which ones exactly?'
It represents checks and balances on the power of the Presidency and at least several additional sets of eyes on things to avoid fuck ups.To put just one example... With the control of both House and Senate Obama was able to sneak in a Chapter IV to the ...
Wait is he a communist? Or a Muslim?  Can you be both? I'm confused.
I would argue that use of the word hate to describe disagreement or disappointment is either hyperbolic or irrational.'
&gt;It's perfectly rational to criticise him for a wide variety of important reasons.correct but that isn't what Republicans do. It's bizarre conspiracy theory and predictions of doom. It's not haters it's hatred. 
[deleted]'
&gt;President Obama today issued an executive order that permits ongoing indefinite detention of Guantxc3xa1namo detainees while establishing a periodic administrative review process for them.ACLU was trying to pull them out via court trials it looks lik...
The generation of fox news watching bigots is dying off.  It won't be long now.  That's not to say there won't be new problems...
You've never had someone nuzzle their nose gently but firmly between your buttcheeks?
Idk if the stock markets the best marker for the economy considering it can rise or fall considerably based on presumptions about fed meetings that haven't happened yet. But I'm not an economist so I could be way off
I think that's the rutting point.
[deleted]'
Correction you can't expect the other party to be more helpful and less willing to block everything you try to pass when the other party's leadership has a meeting the day of your inauguration where they all agree to stymie absolutely anything you try to...
The 2nd grade logic in here is too much to bear.'
Too bad bush and co drove the bar into the bedrock'
It depends by what you measure in. '
The impending interest rate hike is more or less priced into the market. The correction likely won't be very severe especially if it is a moderate hike.I think the affect is going to be felt on a longer time frame as cash becomes more expensive. The effe...
It caught me off guard too.'
&gt; In any event looking at stock market as a direct indication of an economic performance is flat out wrong. That's kind of like looking at the exterior of a car and being able to determine the engine is running just fine.Based on this point alone you ...
&gt; Republicans and Obama agree more often and pass more legislation together than /r/politics is comfortable admitting.lol what? Care to cite that hilarious assertion?'
Well if you take it from the point that Obama took office of course it's better.  It was the Great Recession.  That's comparing the state of a building that's actually on fire to itself a few days later. However there are systemic issues with the current...
but then we can invent golden railways'
GOP:  .....but he is still black..... so there is no amount of success that will make up for that ....'
Sooooo you're saying that the last 6 years of policy wasn't entirely based on Oppose everything Obama is for that he *has* actually improved things? Or is the right side getting credit now? You guys roll back more often than walmart.    xe2x80x9cThe [GOP...
[deleted]'
Well to be fair most of Obama's thoughtful critics aren't in Congress are they? There's a lot of valid criticism to make I've levied some against him myself. But hardly any of it comes from the GOP-controlled House and Senate and their legion of braindea...
So without the transparency platform you'd have been all about the McCain train?
He's also a regular poster on /r/conservative Have a look folks it's the bastion and glory of conservatism on Reddit! nixonrichard wouldn't be making the same criticisms under a Republican president. 
Neither does asphalt. '
Case in point. '
Really? That's the response? Talk about brushing aside the elephant in the room. 
We should be careful. Stock markets have almost certainly exploded into bubble territory fueled by low interest rates and Q.E. Doesn't mean Obama did a bad job he sure as hell did a better job than Bush (anyone could accomplish that). But this could well...
Reagan's first act stalled the economy into two years of high unemployment that included eleven months over 10% and finished with that infamous Black Monday Stock Market Crash inbetween the Farm bubble bust that transferred most of our farmland to the co...
That's just it  presenting facts and evidence isn't about changing somebody's hate riddled mind  that would be an exercise in futility.  Rather such endeavours are about providing evidence to those who have yet to form an opinion.  Every year hundreds of...
Unfortunately this is too little too late. If he wouldve did this like maybe the day after he won his second term and then actually held the GOP's feet to the fire then I would be much happier.
Does he have the authority as POTUS to close it? '
You do realize who is stopping those closures? Right?  RIGHT?  FFS. '
[deleted]'
It wasn't an entirely serious comment but not an entirely facetious one either. I consume a lot of conservative news media. I watch Fox listen to talk radio and read articles from conservative sites. They absolutely attribute every problem they can to Ob...
Just cause you heard all that from the radio talkers and read it on the crazier conservative media doesn't mean any of it is true.  But that can be viewed as a nice summary of the nuttier anti-Obama talking point lies.
Seriously he could single handedly wipe out ISIS Rambo Style restore peace to the Middle East Russian/Ukraine and Israel/Palenstine give every American $1million dollar tax kick back and totally dismantle the American Government and you'd still have some...
Yea that's how the market works. Not going to be a surprise to anyone. It's also why the fed has to be cautious as they do it.
I think that if we did have the internet in the 70s and 80s then things like 8chan and Stormfront would have appeared then too. The racism was always there the internet just allows these people to act semi-anonymously. '
But he got us out of the Great Recession.'
Hes also a christian but you know the wrong type of christian. Dont forget that whole Jeremiah Wright fiasco '
Soooo what I am saying is that Obama hasn't really gotten that much done.  The benefits we are seeing in the economy today are because Obama wasn't able to fuck up the economy because the Republicans kept blocking all his great ideas.'
[deleted]'
&gt; If the people in Congress who oppose Obama focused on that stuff then they might have a leg to stand on.I'm not in Congress. Does that mean I have a leg to stand on?
I'm just pointing out that it's not all Obama and it's more likely due the financial crisis.
You gonna vote Republican and think it will get better?'
[deleted]'
They might have a leg to stand on if they were principled and argued against real issues like spying war and other things that have real impacts on Americans. Instead they're sending letters to Iran's ayatollah throwing snowballs on the senate floor and ...
&gt;People hear what they want to hear see what they want to see and they will continue to push their agenda regardless of the facts. It doesn't matter how much fact checking goes on. Opponents and haters will still find a reason to dislike something thi...
I don't suppose the slow growth would have anything to do with corporate America keeping wages low and failing to reinvest the mountains of cash they have been stockpiling since the recovery picked up right? No it must be Obama's policies.. You know all ...
You forgot fascist.'
xe2x80x9cI can tell you that over a period of four years by virtue of the policies that wexe2x80x99d put in place wexe2x80x99d get the unemployment rate down to 6% and perhaps a little lowerxe2x80x9dxc2xa0- Mitt Romney in 2012.Current unemployment rate: ...
They? What does this have to do with my comment?'
Ok president. Better?'
This just in: Fact checking found unreliable by 99% of Republicans. The words Fact Check may no longer be used in GOP offices. The use of or practice of fact checking shall warrant a full psychological evaluation.  '
because when he was President we were in the world economic crisis since the Great Depression. And now we're bitching about whether he gets credit for having one of the longest periods of sustained growth in U.S. history under his Presidency. 
&gt; And not to be pedantic but'
&gt; *They* pretend anyone critical of the Dems or Obama specifically is a far-right Republican racistYes you said they did you not? Are we discussing politicians or is this somehow irrelevant? I know you meant this sub but the broader idea involved poli...
Republicans in Congress have blocked him from transferring detainees to US soil which effectively makes it impossible to close.[http://www.washingtontimes.com/news/2010/dec/8/congress-deals-death-blow-gitmo-closure/?page=all](http://www.washingtontimes.c...
Yeah see at the time of that debate the claim that Russia was our #1 geopolitical foe was absurd. We had * a fairly substantial nuclear arms reduction treaty * commonly-supported sanctions imposed on Iran (with Russia *still today* willing to play a part...
&gt;The fed does not take commands from the presidentIt would if a president said we're returning the control of the greenback to the Treasury Dept.I'm for privatizing nearly everything but the fuck should we have a privatized treasure dept that's owned ...
exactly'
Who do you think I'm referring to there? Your comment is clearly talking about Republicans and that quote of mine is clearly referring to *people in this sub* who pretend everyone critical of Obama is a Republican.You couldn't have proven my point better...
Does anyone seriously think Romney would have put troops in Ukraine?  '
You caught me. I was definitely trying to be pedantic. Kinda like when people say I'm not a racist but...'
I 100% understand your argument and tend to agree with you.  Whenever I look at market performance I always use S&amp;P.   In reality though the DJIA and S&amp;P 500 have a correlation coefficient of over .95.  I guess you could look at whichever one you...
[deleted]'
Nice [Gish Gallop](http://rationalwiki.org/wiki/Gish_Gallop). And those aren't any of the reasons the right is upset with Obama. 
the issue is that these things only became problematic under Obama even though he is guilty of continuing programs and policies that were started under the Bush administration in a post 9/11 America.'
Lol what legislation has the GOP congress passed that Obama has signed?'
I'm far better off than I was just a year ago.  And several times better off than 6 years ago.
The person I responded to said NOTHING about the right.They were talking broadly about people who hate obama and the immovability of that hate.Also gish gallop?  I wasn't debating.  I was actually agreeing with the comment just in an illustrative way.'
People conflate the two terms perhaps inappropriately because the Venn diagram of Conservatives and People Who Don't Like Obama would be one circle inside the other.
How utterly laughable you're going to argue syntax over content. Let's argue over the word they more instead of the context of politicians while falsely assuming I didn't understand. Bravo! 
Nice save brah'
You are right.  The stock market measures only how well big companies are doing.  Says nothing directly about unemployment income inequality interest rates (sort of) home ownership business ownership etc etc.  It would be really silly to say that when th...
I find racists younger than me all the time and I'm 29. It's plenty of young racists ready to carry the banner.
Had no insurance before now I pay 40 for health and 14 for dental. Best friend had type 2 Diabetes and the Insulin was costing him around $600 a month. Since AHCA he is paying $50 for the same amount.'
I like where you're going with this and it seems like famed liberal mouthpiece [The Washington Post](http://www.washingtonpost.com/blogs/wonkblog/wp/2014/02/19/a-visual-history-of-president-obamas-economic-policy/) agrees with you. So if the GOP can just...
No but transparency made sure that I made voting a priority on Election Day. '
Nope it's all about Nasdaq bro
[deleted]'
[deleted]'
What a curious response.  '
Your comment was automatically removed because you linked to reddit without using the no-participation (np.reddit.com) domain.Reddit links should be of the form np.reddit.com or np.redd.it and not www.reddit.com. This allows subreddits to choose whether ...
This is all controlled by central banking.The President of the United States has zero to do with what's going on with the stock market.
Not to mention that it's empirically false. It's actually hard to dig yourself out of a deep economic hole without taking major damage. Japan has been shoveling for decades and Europe is a great example of how austerity fails.
Funny how he tries to attack Obama from the left.'
[deleted]'
[deleted]'
They all lie != they're all alike'
This is a worthwhile debate.'
&gt; He's also a regular poster on /r/conservative[1] What a load of crap.  Where you do you get this stuff about me?  I bring up criticisms of Obama by the ACLU and I get accused of being conservative.I just checked my comment karma breakdown.  I've got...
Clinton was almost impeached for fucks sakes and he still found compromise! Your rationale makes sense if the Presidency was like high school where you refuse to talk to people because they say mean things to you. But that's not how leadership works.Yes ...
[deleted]'
and the fact that he is the most progressive president since carter is a very sad thing as he is a moderate at best.  it shows how much our politics has shifted to the right over the last 30 years.  '
You're only under the illusion that the economy is better. Band aids fall off eventually. 
That intolerant bunch.   I'm the tolerant one so let's kill them... 
Still the worst president circa Woodrow Wilson'
My argument was about valid criticisms of Obama and nobody seems to have been able to refute them.'
&gt; encouraged hiring part time over full timeThat's a Republican talking point not supported by the data.[Chart 1](http://i.imgur.com/OvrTfeV.png) that I made (pretty crappy graphics i know)[Chart 2](http://i.imgur.com/tXPHlhE.png) from 538.
Nixonrichard use to whine about The elitist obama appointed to the FCC now he has switched to vaguely whining about banking.'
Point proven. Please provide moar maymay's edit: BTWSyntax defined: the arrangement of words and phrases to create well-formed sentences in a language.You you must understand this at minimum right? 
[deleted]'
[deleted]'
  That makes no sense at all. The argument is the market is a predictor of the economy and a sign of its health. When the market was 7500 it was bad. Now it is much better that is the whole point. You predict a crash and think that is relevant to the mor...
Because that is what this article is about. '
Cool but I wasn't responding to the article I was commenting in a thread where people were making general statements about Obama's critics.
Am I missing something?   I was agreeing with the parent.'
&gt; the S&amp;P 500 is a much better indicator of the markets.[I'm not seeing much of a difference](http://research.stlouisfed.org/fred2/graph/?g=14W7)
New GOP talking points: *Sure the economy--using any conceivable economic indicator--has improved a lot since Obama took over. And sure we were wrong about every apocalyptic prediction we made. But trust us it would have been even better had the GOP been...
It's funny how this goes from 800 to 1500 in 20 minutes and reddit does nothing about blatant brigading.Edit - Another 600 in 40 minutes. Gonna stop the brigade mods or do the rules not matter here?
None of what you said in any way diminished the criticism.I don't know how doubling the number of prosecutions of leaks and whistleblowers and rejecting more FOIA requests than Bush and bombing more Muslim countries than Bush and sidestepping the War Pow...
Except they bred and raised their kids on Fox news. I'm just lucky and got out and opened my eyes
Sometimes I wonder if that is actual reality. I'd love someone to come here and lay it out. We're the things blocked by the pubs potentially damaging making them the heroes of today or is it the other way around where current improvements  are due to som...
  And Obama is still black.'
The article is about conservative hatred and Obama his comment was obviously related to that context. Apparently you are admitting to off topic derailing of the topic.'
Do you think the ACLU was lying about Obama signing the executive order?The fact that the link was an ACLU article about Obama doing it doesn't mean it didn't happen:http://www.washingtonpost.com/wp-dyn/content/article/2011/03/07/AR2011030704890.htmlI do...
You're injecting a lot of emotion into an otherwise thoughtful conversation.  Try not to be so sensitive.
They would be valid criticisms given an alternate reality for context. He is called a dictator now how would people react if he made any of those decisions on his own without the approval of congress? And you are being disingenuous to imply that those ar...
You're right people hate Obama for everything you listed...except...Obama is currently trying to get a new war powers act passed; indefinite detention is backed by congress by not providing funds to close gitmo; not everything the government does should ...
So I'll put you in the denying reality column then.
You mean the kid who's father was part of Al Queda correct? Yes I agree that the government should not be drone bombing American citizens without trials - but the way I see it this is little different than someone bringing their child with them when they...
No they were saying the people who hate Obama do not have opinions which are swayed by facts.  That IMPLIED irrationality but the whole point of my comment was that not changing your opinion about Obama due to facts could be because you have legitimate g...
I don't vote for Republicans or Democrats.  Are those the only choices you believe are acceptable? 
Forgot the /sarcasm tag'
*goes to great lengths discussing the current administrations policies**someone mentions republicans status quo on said issues**crickets and a fickle response* '
The GOP suffers from cry wolf syndrome. '
&gt;The impending interest rate hike is more or less priced into the market.Care to explain that?  I'm not means a financial expert but if people pull money out of the stock market it will drop...  People who have 401ks or Roth IRAs will feel the impact ...
Well there is that whole thing where he passed basically what the Republicans proposed as an alternative to HillaryCare in the 90s.  '
Like hatred of Bush?   If I dislike and disapprove of someone why is it hatred? '
Yeah what an idiot it's like he didn't even know that the Congress that gets elected in 2010 is sworn in in 2011 meaning that the Democrats still held a majority of both houses on that date. Oh wait it's more like he did know that and you didn't.You also...
Agreed. We always vote for the politicians that lie the most effectively to our core principles. It has always been thus. The only difference nowadays is that the media does the same.'
&gt; What about Obama giving discounts to people who are destroying your environment? You won't need to worry about healthcare when you don't have anything to eat?  How much is it Obama and how much is it Congress? I'm more worried about his bombings bei...
I thought it wasn't that everyone was having too much but that most people were having too much and weren't paying any attention to the lower classes?'
And the experience to know that candidate Obama was way too naive.'
Nice deflection but I found the brigade site. ;)'
Well what I'm getting from that graph is that citing the S&amp;P 500 would have made Obama look ever so slightly better so there's that.
Do you honestly feel good about making that comment?  Many many people are struggling due to the huge economic meltdown of just a few years ago.  For many of us it is not over.  Many struggle just to make ends meet and do not have money over to be able t...
[deleted]'
When I went to church the pastor used to say things like heaven's  streets are lined with gold and everything is so awesome.1) if gold was that plentiful it'd be pretty much worthless2)gold streets would look ugly3)why does heaven have streets. Can't we ...
No he hasnt. Nothing he has done is good for wall street.The fed which is apolitical has been great for wall street.'
Yes because conservatives would surely have stopped that if they had a chance.  (Please don't go looking at voting records on these issues please don't)
I assumed he meant rust but rut makes sense here.'
Not just do but do very well.'
The only two things that have this economy booming right now is fracking and the fact that the dollar is doing well. Nothing Obama has done legislatively has helped our country at all. He is the worst President since Woodrow Wilson'
Oh no! Not one but TWO libertarians spent their allowance on reddit gold for your comment so it must be true!! :D'
The stock market means nothing to me. Look I'm all for a stronger economy but when that supposedly stronger economy(stock market) politicians prattles on about like a bunch of parrots does absolutely nothing for me and my daily life then it's meaningless...
No one knew and still don't know what to do with the detainees. Are they tried in a military or/and civilian court? Where do they go afterwords? You're still correct and even Obama admitted it saying he wished he had closed it down his first day as presi...
Let's also keep in mind people hated Obama before he played a role in any of those things.
Wouldn't your friends just say that their (conservative) team America in congress stifled ol' Barry's Marxist agenda?
We would be at war with Russia Iran and Syria. '
[deleted]'
Two wrongs don't make a right.
Since the fed has given guidance that they will be raising interest rates the market already reflects this. In other words the market isn't as high as it would be if there wasn't an impending interest rate hike.There may be some retail investors pulling ...
Why would they do that? If they cared about evidence and history they wouldn't be part of the GOP.
The ones that say suck my nuts im gonna what I want regardless of what fox news thinks!'
You forgot foreign born. They are *still* preaching that and it still makes rounds on every conservative hugbox on Facebook.'
This. This 50 times over. At a time when America needed DRAMATIC change we didn't get it. And we won't get it in 2016. Obama will go down as one of the worst 3 presidents we've ever had but the liberals will continue to play the race card. 
See here's the thing. I think that many of them are just doing their master's bidding. So it's not so much that they're uneducated (at least the GOP leaders anyway) as that they're running an agenda. Their agenda right now is to extract as much wealth ou...
In that case you must be mad as hell about the things the GOP does.Hello? Hello? '
by the people for the people is now by the workers for the shareholders'
[deleted]'
Says who?'
Sure it's called voting.
&gt; industry insidersIf you take the bad in this and you should you should also take the good. I for one was absolutely livid that Wheeler got the FCC post. Particularly after his first few missives concerning Net Neutrality were frightening. I'm sure y...
[deleted]'
Oh please stock prices are up for two reasons: 1) the federal reserve pumping insane amount of money into the economy to allow housing prices to reflate so people could refinance from bank owned loans to publicly guaranteed loans and 2)companies buying b...
HAHA Yeah poor family http://en.wikipedia.org/wiki/Anwar_al-Awlaki I'm pretty sure you have no idea what you're talking about. You're just spouting stuff you've heard as talking points with some made up story from your own imagination filling in the part...
[deleted]'
[deleted]'
At least injuries heal under a band aid. Sure beats the gaping chest and head wounds the GOP left us with in 08.'
I'm not the OC but I think he is drawing on the efficient market hypothesis which basically means that everyone has the same information and the market will work itself out. When he says  &gt;the impending interest rate hike is more or less priced into t...
A lot of these thoughtful critics tend to combine the criticism you mentioned with pretending the two sides are equally bad.Maybe people would take it more seriously if you didn't have to resort to hyperbole to make 3rd parties seem like better options.A...
You must be popular over there! '
#'
I don't think it's that simple. Are people so quick to forget the vitriol with which the GOP attacked Clinton in the 90s? Do people remember the Clintons calling it the vast right-wing conspiracy?Racism is just a convenient tool for their radicalism not ...
&gt;it's more likely due to the financial crisis.   Which we all know was Obama's fault/s
Which is what has given rise to more extremists on both sides of the aisle.It was all hunky dory for the longest time when the parties worked together but that doesnt mean that they were working together for us.'
Not really. Like all libertarians you vote republican while blaming Obama when his policies mirrors theirs. If your criticism of Obama was *really* because you disagreed with him so much on the issues then you would be critical of both sides equally but ...
Stocks go up when your money is worth less. '
Yeah but the context was always speaking to *those people who cannot be swayed by facts.* No one was suggesting that it's absolutely impossible to have fact-based reasons to dislike Obama. If even *one* person had said that I would totally agree with you...
Does Obama also get credit for the value of the dollar falling? '
Wait.. do people actually think the stock market is a metric of the health of the general population's wealth or well being? I sure hope not. 
wealth inequality has increased since 08.  That's an example
And yet as far as civil liberties go Obama called down for an industrial  grade drill.'
Gold would in no way rust. Malleable yes'
&gt;it shows how much our politics has shifted to the right over the last 30 years.Right but rather than whining about it progressives should be out there voting. Progressives have the worst voter turnout.If voter turnout was equal among different demogr...
Well first off let me say that I don't think Republicans are heroes.  I'm just sick and tired of Democrats saying how great Obama is when I just haven't seen it.I do know for a fact that the economy has gotten better.  Democrats want to take the credit. ...
&gt; My argumentYou haven't actually made an argument you're just throwing words out to see what sticks.   You've repeatedly implied that you've made some sort of argument and we're all waiting to see what that might be if you decide to share it some day.
That's cute because you are the circlejerk. Reddit loves to come here and shit on this subreddit and Obama and like you they add absolutely nothing to the discussion. I would love to here what your reasons are for hating Obama but I am 100% sure you have...
LOL so you dont have any idea what your talking about.  I should have known from your first reply.'
Do you have a timeline for this supposed second housing meltdown? Or this is another one of those GOP scare stories that has yet to come to fruition? As for worker wages they've been stagnant since well before the Obama adminstration so you're going to h...
As the other person pointed out you're correct. The stock market really has nothing to do with the economy. It does in the sense of traditional Y=C+I+G-(X+M) {output =  consumption plus investment/saving plus government spending minus (or plus) imports/e...
A vote for any other candidate is as good as a piece of trash.  The current set up means you vote for the one you *most* agree or nothing'
[deleted]'
This is actually a pretty insightful and intellectual comment.  Republican leadership is just stroking the natural hatred and racism of their base in order to enrich themselves and their corporate owners.  When you look at the Right wing through this len...
Why are you here?'
[deleted]'
See I disagree. I am far from the 1% and I am doing much better than I was 6 years ago'
It's not a matter of naivete candidate Obama was hardly naive. It's a combination of the compromise that comes with the job - no **modern** president has ever fulfilled all or most of their campaign desires - and the inordinate hostility that *this* pres...
Libertarians are Republicans who want to legalize drugs.'
The title of the post is literally Obama on GOP critics. How is that in any way a curious response?'
We're doing better from a Wall St point of view but not necessarily as well from a Main St point of view.Too much time and effort has been spent on rebuilding Wall St when much more focus should have been put on encouraging job growth.'
If by acceptable you mean have a nonzero chance of winning a national election then yes.'
[deleted]'
Objectively I think your first graph is pretty obscure. The first bar shows very low part time employment and jumps up greatly right during the time of the recession but doesn't diminish greatly to the next bar. If this were a debate I wouldn't have even...
The Stock Market is NOT a viable way of measuring the economic outlook of the nation. Majority of the people in a nation does NOT own stocks.Majority of stocks are owned by Very Very Very few WEALTHY people not a large middle class collection as portraye...
 Obamanomics  101     stockmarket=economy.'
When the boomers start selling their homes. We've got too many suburban ranches and McMansions for the generation behind them. And while wages were stagnant before then it doesn't mean they needed to be stagnant now. Companies are flush with cash and bor...
His foreign policy is disgusting. Expanding war in what 7 Muslim nations? Stepping up ineffective drone strikes that kill innocent people?'
[deleted]'
All those things are fallout from Bush's almost collapsing our economy.
Like making a deal with Iran and reforming immigration policy? '
&gt;He's not up for re-election anymore... Why the need to still keep throwing jabs at the GOP? He wants folks to vote Democrat in 2016.'
Dollar is strong as fuck right now.'
Not a clue! I just know the other side sounds borderline insane so I'm gonna stick it out over here 
Democrats boggle the mind they cheer on Warren and Sanders going against wall street and then turn around and cheer for Obama bragging about a stronger wall street.'
&gt; Yeah like those morons who hate Obama for executing a war in violation of the war powers actWhich war? Because last time I checked we haven't declared war on anyone for a very long time. You mean military operations those things every President has ...
So vote for the candidate whose policies I don't agree with at all or vote for the other candidate whose policies I don't agree with at all?
Ironically the economy did just fine under Bush until the democrats took control of the house and that bitch whore Nancy Pelosi became the whip. Obama never started doing well until she left. The bailouts fixed this country. Obama has made one wise move ...
And yet the indexes track incredibly well.  Its not without flaws but its far from broken or wrong.'
/r/politics does not like Wall Street but uses it as the gage for Obama being successful on the economy. '
Anyone in America with a non liberal arts education and money in their pocket. '
That doesn't make sense... that's what I do i vote for the candidate i most agree with. It's very rarely a Republican or Democrat. 
Those are the **only** choices on the national/federal level. In a first past the post electoral system there are only ever 2 parties. That's a fundamental property of such a system. [It's been proven](https://www.youtube.com/watch?v=s7tWHJfhiyo) with st...
If the stock market was tanking can you imagine the GOP freakout that would be going on?  Then the stock market would be a key metric of how the economy is doing and a cornerstone of Obama's war on capitalism and job creators.  But since the stock market...
donvotes are because you're supposed to tout Obama's successes and ignore his failures. You did it backwards.
This is also not the point. Obama is not claiming that the stock market's health is his doing. He's saying that it's healthy and that the dire predictions made by his detractors haven't materialized. If someone says a meteor will hit the Pope if this guy...
[deleted]'
Obama is scumQE is all you need to know You said we'd run out of money but we printed more you doubters!  Idiot president.  Awful president.  Pure dishonesty'
Good for you (full disclosure: I had nothing to do with that) but it still doesn't mean you're not judging the merit of the information on irrelevant grounds.
Because you're cherry picking your examples. Obama also passed credit card reform Obamacare reduced year over year defense spending by 33% increased spending on veterans reversed Bush's executive order of embryonic stem cell research eased restrictions o...
Benghazi Benghazi Benghazi.'
It's also not controlled by central banking.
If we gave CUTS? And CUT taxes.. Our.. deficit.. would be in a better place?You retard. Do you even math?'
Every presidency sees a little bit more of our civil liberties chipped away. And the thing about liberty? It only gets lesser never greater without revolution. '
Dutchman here we love it. Americans buy our crap like crazy.'
I appreciate the constructive criticism.  You are correct part time employment doesn't diminish much during the Obama administration.  But if the argument is that Obama's policies encouraged hiring part time over full time then I would expect part-time e...
And neither the Dow Jones nor the S&amp;P 500 is a very useful indicator of individual peoples' economic situation save for those who are retired and primarily living off of their IRA.
That's probably true. Slavery was probably the only acceptable economic model.  We probably had to put Japanese Americans in camps during World War 2. We had to start a 15 year war in Iraq. You can do what's pragmatic and tell yourself that you're trying...
OBAMA SAY WHAT IT DO'
[deleted]'
I too took Macroeconomics.I too am not a real economist but I don't pretend to be.
There was a good piece about how the Obamas have gotten a lot more jaded by their time in the White House. They're a lot less trusting they're a lot more careful. Obviously I don't disagree with you but I think it's a combination of both.
That was my first thought. Haha. That or not to be a dick... but...'
Chronic wage stagnation and growing inequality tend to result in people getting pissed when the rich keep getting richer. '
I can't speak for other progressives but i have voted every single year since i turned 18 12 years ago.  
The millennium generation is fairly large. However I'm not sure how many exurban McMansions they want at the end of a 1 hour commute.
The GOP convinced millions of people that Obama was an agent of some foreign state hellbent on destroying the nation.  They claimed that he was setting up death panels. They claimed that he wasn't American.  They claimed he hated white people.  They clai...
It's an aggregate indicator. You're absolutely right in the sense that it doesn't provide a complete picture. It's like saying this year was the hottest on record and you're sitting in Bangor ME saying so fucking what? So yeah a healthy stock market does...
The problem is that the guy you're replying to is a notorious neocon. Sorry I mispoke he's a libertarian (I think). /u/nixonrichard *doesn't want* this country to move to the left. He wants us to move further to the right. He's all about states-rights an...
[deleted]'
What?Jesus... I'm just saying it's pretty weird to stand on an accomplishment that wasn't really directly his.  
I think he's a zombie too.
You're the minority. Young people and poor people generally have terrible voter turnout in the US.http://courantblogs.com/investigative-reporting/wp-content/uploads/2013/05/Election2012_ByAgeGender.jpg
And for many other reasons including market confidence which is one of the more important ones. '
[deleted]'
I'm sure the Occupy Wallstreet folks are pleased about the stock market closing over 18000 Mr. President. I know I'm richer but I think you are confused. Do you actually think you had something to do with the stock market rise? Here all along I thought i...
It actually makes perfect sense.  Your vote for a candidate that cant win with whome you agree 90% with takes a vote away from a viable candidate with whom you agree 65% with.  You're only helping the chances of the candidate you agree the least with.  3...
GOP: not racist but #1 with racists.'
&gt; Which is what has given rise to more extremists on both sides of the aisle.Name some left wing extremists that are the equivalent of these right wing kooks that are currently in congress.'
[deleted]'
I agree with you to a certain extent but his own admission was that he was naive.'
Should've added a sarcasm tag. I thought I didn't need to lol
So you're saying gold mates with ruminants? 
The economy would have improved regardless of the political party of the president. The fact is no one who is elected to the office of the presidency is going to let the country fail. Whatever needs to be done will be done. This pattern has been repeated...
Cactus from mars also.'
That's the weird thing though - there are plenty of reasons not to like Obama.  They just happen to be things Republicans secretly cheer on so they have to fabricate stuff instead.
So ... you not only have a problem with Obama but you also have a problem with anyone with a liberal arts degree?   Educate us as to why.'
[deleted]'
Both sides are the same! If we ignore all the specific things Bush fd up then yes just like that.'
You can usually spot the libertarian in a thread when you see the line: *Both parties are the same. People need to vote for a third party in!*Well that sounds interesting. Which third party is this person talking about? (Looks through their previous comm...
&gt; How many of you non stockholders are richer?Me?'
I think history will be incredibly supportive of Obama. Obamacare is a big fng deal. So is his foreign policy position of not just invading shit. '
[deleted]'
The stock market won't be doing well for long though. This is obviously a bubble and the chicken's gonna come home to roost within the next few years. We need to stop allowing banks to invest in stocks; this happens every single time. Obama isn't even go...
See that's your problem  You're thinking for yourself.
Uh... what am I in an alternate dimension?'
Very good point. '
I wonder what has a larger impact on the economy: The stock market closing on one specific day or the fact he doubled the deficit. That requires logic and not emotional knee jerk reactions so I'm not sure if y'all will get the right answer. Hey look ther...
These are all valid criticisms of Obama. Good job. Now if you could get the rest of Obama's detractors to also focus on what he's done as opposed to what they think he stands for and apply the same criticisms to people on their own team we might be able ...
I feel like we need another civil rights movement that will rid us of Republicans and Conservatives. They are truly some misguided uneducated and hate filled folks. This is exactly the language and attitude that we need to get out of politics. Doesn't do...
I like how you admitted it.'
They talk out both sides of their mouths. He's both a radical who has changed the country beyond recognition with his leftist Machiavellian schemes and an incompetent do nothing who can't get anything done due to his inexperience and idiocy. 
The burn is palpable. If there wasn't a my team your team ethos in american politics nobody would vote Republican. They have zero credibility on the economy zero trustworthiness about war or international relations and nothing to offer the middle class o...
Please be civil. Consider this a warning.'
Where would you find this? MSNBC of course. It's nice to see that Reddit has a cable network. The stock market is on steroids and they are called QE infinity. I pity the fool who thinks the DJIA = economic anything except for wall st.
[deleted]'
For me it's not really that Obama is all them great.  American politics as become a choice of who is the lesser of two evils so the question is more how is less bad than who is better.
If the GOP was in control these past 8 years  half of the middle class would be unemployed or be on the street and all the programs to help us would be defunded'
you realize this makes you no better than them right?'
&gt; progressive policiesIn fact as Washington Post Fact Checker Glenn Kessler noted the biggest backlog occurred in the 110th Congress in 2007-2008 when the Senate failed to act on more than 700 bills. And that was when the Democrats controlled both cha...
Agree with the second part but not the first. They are valid criticisms even if they have reasonable answers. I think it helps to admit things like this in order to have conversations with opponents in good faith. '
&gt; Ironically the economy did just fine under Bush until the democrats took control of the house and that bitch whore Nancy Pelosi became the whip.I've heard right wingers repeat this charge over and over again...that it was the Democrats in Congress w...
Were we not always going to have the problem of a flooded housing market when the boomers get shipped off to nursing homes?  Would it be better to keep housing prices low or artificially low so that the surplus homes get purchased?  Maybe when the McMans...
The opposition *can't* oppose obama on illegal war indefinite detention lack of openness appointing industry insiders spying on americans and staying in Iraq ***because those are their playing cards.***  As baseless as much of that is if they moved again...
In a discussion of how much he's done without support from Republicans your comment seems childish and petty.  
[deleted]'
Obama is taking the easy route by pointing to failed GOP predictions rather than his failed promises for middle class recovery. '
[James K. Polk](http://en.wikipedia.org/wiki/James_K._Polk#Presidency_.281845.E2.80.931849.29) filled all his campaign desires in his first term; he decided my work here is done and never ran for a second term.'
Admitted?'
If only the elected could be like this all the time instead of spending all their time in office focusing on winning the next election and berating/defending themselves against the competition. Think of the power you have in those positions to change thi...
The Iranian nuclear deal is fucking huge if it works'
Wasn't one of his campaign promises not to run for a second term if he fulfilled all his other promises? 
Okay but how much longer do we have to wait? Numerous conservative economists (you know the Austrian ilk) have been predicting massive correction each year of Obama's presidency. So what is soon? A decade from now? Three? Seven? As far as I'm concerned s...
Just because the Federal Reserve dumps trillions of printed dollars into the stock market doesn't mean the economy is good or that unemployment isn't shit. [This pre-Fed image accurately predicted the system](http://williamdudleybass.com/MyBlog/wp-conten...
[James K. Polk](http://en.wikipedia.org/wiki/James_K._Polk#Presidency_.281845.E2.80.931849.29) our 11th President. When he took office on March 4 1845 Polk at 49 became the youngest man at the time to assume the presidency. According to a story told deca...
Mitt Romney's 6% by 2016 is my favorite. That's gotta sting.'
It absolutely is second grade logic to say because the other side did this first rather than justify your own position. Attacking my grammar is a fallacy in and of itself.'
it rises and falls on the algorithm of machines let alone actual policy'
Apparently you haven't been to Michigan. Id take gold roads in a heartbeat 
YAY!!! During the Bush and Obama administrations a stock bubble has been created by pumping the markets with free money and holding interest rates at zero. Who would have guessed stocks would have gone up artificially? Crazy talk. Bush was so smart. Obam...
There are some really hard 18 carat gold alloys. If you textured them up all rough they'd make a pretty good road surface it think.
With essentially zero republican votes through a dodgy procedural means to end run normal cloture rules. '
I prefer Senator Obama. '
It's more like if a parent forced their kid to go with them on a bank robbery. The parent is killed during said bank robbery. You then find and kill the 16 year old two weeks later for the crime of not getting himself out of the shitty situation quickly ...
Could you not stand MSNBC being controversial of George W. Bush DAY IN AND DAY OUT? This is why I cannot understand you liberals on this sub. Honestly you are not smart enough to see your own bias and it is baffling. MSNBC is the equivalent of Fox News g...
communistsocialistnaziatheistmuslimkenyan'
I suspect a lot of reddit was too young to care about politics during the Clinton era. I agree though. Obama's two terms really have been a continuation of that same manufactured outrage industry developed by the right wing. Racism certainly features in ...
[deleted]'
I wouldn't I'd be out making potholes and paying off my student loans.
Since when have facts or reality bothered the right wing?  Remember how proud they were to shut down the government?  But when they realized they pissed everyone off  no it was Obama not us but....but...we have you on video last week saying you were prou...
I agree. Obama's war policy IMO is one of his great faults. I think the US has grown weaker over the years since Obama took office. I would argue you need to be hawkish to contain other nations (Russia China). Fighting Libya and other small skirmishes is...
You're not seriously comparing President Obama to Polk.  A man who served as President during a time when America was only 27 states didn't have the same defense foreign policy and financial complications we have now.  A man who served when both houses o...
He is also going to be down in history as the climate change president! While people on the right are still denying it exists..'
Musmmunist.'
Stock market isn't the best indicator of a healthy economy... Unless of course Obama says it is then we party!'
That's not actually the context bro. The context is as follows: Conservatives blamed President Obama (largely exclusively) for the state of the economy. One can infer from that harsh criticism that they believe presidential policies are able to determine...
They're ok with that Jesus was a zombie.
[deleted]'
Yah and since the stock market is his only real metric highlights how bad he's actually handling the economy. That's like saying a patient is healthy because they are breathing. 
This is a very insightful comment. Here's an upvote.
I would never blame or credit Obama with anything since the President is just PR for the military industrial complex.However I do love how he talks some shit!'
The ACA was endlessly compromised to meet the demands of the GOP. It's just as much their plan as his. 
That is not just a Republican talking point. The proliferation of part time work is a real tendency. The Republicans' turning into their talking point by saying it's the fault of the ACA. Which is stupid. But I digress.
Too bad the next administration didn't have the political capital or will to live up to their promise to do something about it. 
You're the first one to mention John McCain filling in your own blanks..
I would fucking love that.'
No but look who he put in there. '
I'm finding this difficult to respond to because I don't want to spend my day on this. Yes they are valid criticisms but we cannot ignore the fact these are not the mainstream criticisms that they tend to be held by those on the left more than right and ...
People will say now The president has no effect on the stock market and then next time it takes a dive they will immediately flip and say See? The presidents policies lead to a weak economy. You see it/hear it/read it all the time in conservative media. ...
Of course his administration dumping some 87 million dollars into the market a month plus holding down intrest rates market better be doing fine.'
They are simply addressing the statement above which claimed that no president had accomplished all or most of their initial goals.'
Haha yes that's true.  It does rut though.
Of course not. I just get ticked when people make statements like OPs. '
To be fair they're gonna call hilary or Bernie sanders communist radical conceding to Islam elitists just like they are with Obama. It doesn't really have to do with skin you should see how they've been to Hilary with Benghazi 
As much as I hate to think about it I could see that happening. '
So we're debating on their terms? Saying equally inaccurate things for the sake of exonerating Obama?And people here wonder why the conversation trends towards the right. They dominate everything down to the manner in which you even talk about politics. 
I'm not saying the other side did it first I'm saying the other side made the rules in terms of critique. Then they want to change them when the result is something positive after they preached doom and gloom about the administration.'
The most religious and uneducated live in small towns in rural areas not suburbs. Suburb conservatives are about one thing and one thing only - keeping our money. '
Just bombing countries is much better than just invading countries. Unfortunately I might have high expectations from our leaders as just not invading shit isn't enough to qualify someone as a good president. IMO
There are plenty but not enough to make the effect that the elderly have. Most frat bros are conservative but other than that it's not common to find them at college
I think we hang around the same subs because I've seen you around a lot recently. I disagree with a number of things I've seen you say but you're right here. I would like to see less blind defense and more honest discussion in all of the subs really. Thi...
I think of it more like there are platforms I find completely unacceptable.  so i won't vote for a candidate who supports something like oh say endless war torture drug laws and supporting drug cartels  spying on innocent citizens  etc. So you know  ever...
So when he promised for more transparency and then blocked foia requests for the White House that was a compromise?'
I actually don't hate Obama. And I'm a liberal. But the truths behind those accusations are the things I don't like about him. The circlejerk thing was purely meant as irony. But of course assume that I came to shit on this sub and don't know what any of...
I would say most people have some kind of money invested in mutual funds a 401k or IRA.'
Looks like he fulfilled at too then.'
fair enough.'
Lets vote for jeb. I love the tastes of shit.'
I'd prefer a Democrat-controlled Congress that can legislate without being held hostage by Blue Dog DINOs. 
Exactly. The market and the economy are two different things.'
I said that in the 90s...'
Dude! Or ma'am! No way? Where did you read this? This has been the most vocal criticism of Obama from my conspiracy-obsessed friends. And I wasn't happy Obama signed the bill. Could you please provide a source or lead me in the right direction to look fo...
And racist against black people... and white people.'
and as we saw from a mod doing detective work make it seem like there are more than there really are.'
Not a lot of (any?) good presidents are there under your criteria?'
But the stock market when compared to inflation is still exceedingly mediocre lol '
You lie was during a joint session in the run-up to an ACA vote not at the State of the Union. '
The same Obama that is going back to war and going after whistleblowers? Clearly this is the best Obama.'
I stand corrected.'
If the roads were paved with gold the value of gold wpuld be so low it wouldnt be worth stealing'
OK fair enough.   If you're using non-stockholder as shorthand for someone who's unemployed or who has a low paying job with little savings then you are correct they probably don't feel much richer.   Although many of them probably do feel better off eco...
[deleted]'
I wish we could amend the constitution to have Obama 2016. I'd stand for 8 hours in a line to vote Obama in 2016
I guess no one here knows what a Federal Reserve fueled asset bubble is. '
He's still the uppity black man in our White House to many.'
I don't disagree.
Run the tape back.This needs to happen more.  For all politicians.'
&gt;Republicans care more about hating someone for the color of their skin than the country that elects them to Govern.  I know right? Like all those black republicans and republican supporters are actually white people doing black face. That way the par...
Had McPalin or Mittens the Pander Bear won we'd be far beyond ineffective drone strikes. We'd actually be at war - not drones but large numbers of US troops on the ground - in at least Iran probably Syria and maybe even be up to our necks in the tribal a...
that's got shit to do with the current economic situation. unemployment is based on how many people are claiming benefits and doesn't include people who have used all theirs already or have given up trying to find work and don't apply for them.labor part...
&gt; Expanding war in what 7 Muslim nations?Yeah but he's making them fight each other grinding up the worst extremists without as many American casualties.  Genius!
THANKS OBAMA! '
That is my point. '
[deleted]'
&gt;for denying record numbers of Freedom of Information Act requestsThis is misleading. The only reason there is a record number of denials is because there have been a record number of requests nearly half of which are from private citizens wanting to ...
This subreddit has got to be run by mods with an agenda. There is no way this shit is real. lol. This entire subreddit is a ringer.'
To be fair Bush actually started wars and sent people to die. Obama has managed to not start any new significant conflicts.'
I'd prefer Obama for 2016...
You are totally right. There is no need to be disrespectful to you. Especially if you are here with an open mind and ready to debate your claims with a professional attitude. I'll take one of your claims on. Obama according to you has expanded war into 7...
The GOP would have boots on the ground in 7 Muslim nations if they had the chance.'
That was not at all considered the biggest problem. The huge financial hole Reagan dug was a lot bigger problem.'
My take is different from the civil rights movement. We had Nader Zinn Chompsky MLK X Parks etc.; who didn't just give speeches; it was ther tireless efforts of changing (rallying smart young individuals from higher education engaging foriegn leaders whe...
Well you don't have to spend all day on this because I'm not one of those ignorant douchebags who will run a debate into the ground. I also share your aversion to engaging with one. And I agree with you! How's that? The thing is I'm trying to do *my* par...
Pandering to the KKKristians is secondary to fellating the corporations and billionaires.I serve those teabags every week remind them what Jesus would really do and striving to bring my party back to the principles of Eisenhower while knowing it is a foo...
You think we have to be blowing shit up in the middle east to keep China and Russia at bay? Russia couldn't afford to go to war if they wanted to and China knows if they declare war all of the money they get sent to pay our debts is going to stop coming....
s/Benghazi/EmailGate/g'
The GOP has to blame Obama for the average person's state of affairs otherwise the spotlight would gaze over to the people who are actually responsible for most people's shitty lives. 
Which was completely expected due to baby boomers retiring...not sure what point you are trying to make exactly except that peoples predictions about labor participation rate came true.'
rutting here is I assume an emphatic word used similarly to fucking.'
Bad things happen because of Obama. Good things happen in spite of Obama.'
The democratic party sent me a letter asking for a donation and listed all the gop claims that President Obama proved incorrect. '
[deleted]'
Don't forget...\*shudders\*...*Kenyan*.
Would be awesome if you could find a source on this '
I think the drone escalation was his worst action but much of the rest of his foreign policy was great. Geez he ended the disastrous war with Iraq which his 2008 election opponent was dead set against.'
[deleted]'
I feel like I've been put on notice - lol
You're right. The S&amp;P shows how much better it is as a market indicator. http://i.imgur.com/g1rjiGc.jpg
[deleted]'
&gt;The stock market really has nothing to do with the economy. Once you wrote that silly statement I had little interest in your opinion. nothing?'
obama to take over the daily show after stewart?'
[deleted]'
&gt; black republicanslol'
Because you haven't offered anything. Just a list of things; not all of them even his fault.
My primary fear about Romney being elected was that the economy would right itself (which was going to happen no matter who was president) and Romney would take credit.'
My goodness! Another doom laden prediction! Yummy!'
Not if you measure from the day Obama took office.But that's beside the point conservatives predicted the economy would tank under Obama so even mediocre is way ahead of conservative prognostication.
Printing money would not make the stock market rise. Actually it would likely make it drop.'
Not sure if pun very subtle Firefly reference or a little of both.'
How did Obama double the deficit? '
True enough but the economy is improving and wealth inequality is increasing are not mutually exclusive. It would be better for the economy if middle class people were seeing more prosperity but we all know that if Romney had been elected the CONSTANT cl...
Which of course has nothing to do with the retirement of the 2nd largest generation in American history....And neither U3 nor U6 has anything to do with the collection of unemployment benefits.'
You're missing the point entirely. The sole point is that conservative predictions were wrong.
Dude's too classy to give a fuck. 
And people still eat his shit up. Shows how easily misled even fairly educated people can be when its spoken like a forceful fact. '
[deleted]'
It's the metric we've used for years.  If there's a better measurement then offer it.  Had this trend happened while Romney was President he would undoubtedly be making a similar  speech. 
[deleted]'
reforming immigration policy - as in making decrees that go directly against US law? '
That's very true. I think repubs and dems are both greedy Bastards though 
Congress couldn't possibly manage the economy. Doing so would result in an economic disaster of unprecedented level. 
Obama is getting a message out there or Democrats to run for 2016.   I believe they call it setting the narrative or something like that.   '
unless all the money was given to wallstreet.'
Yeah too bad it took 8 years and now all this weed should be legalized and opening trade relations with Cuba is to help out the Democrats next year.'
This may seem like nitpicking but if he were murdered his daughters wouldn't be orphans they'd still have Michelle lol. But I agree with everything you're saying.
No. Wealthy people will buy them up and create new plantations for their minimum wage ~~sleeve~~slave labor to work on in exchange for minimal housing and food (provided ever so much more grimly by a government rent subsidy.)EDIT: STUPID AUTOCORRECT.'
They are certainly related. You're misinterpreting what you're reading.
Circa means around or approximately.'
it can never just be Obama's fault can it? it always has to be but BUSH! or blame Bush for that! there really is zero accountability among Obama fans. it's ok for the guy to have been wrong or lied. you don't have to pass the buck. '
You're completely missing the point but that's to be expected.
Quick breakdown of Obama's points:Republicans claimed Obama would diminish employment and stock prices.**Reality:** Stock market more than doubled. 12 million new jobs. Republicans claimed Obama would cause trillion dollar deficits as far as the eye coul...
Republicans care more about hating than anything.'
I used to be a staunch republican mostly cause my parents were like that and kind of brainwashed me in a way but I am seriously loving this new Obama. I mean he's not perfect but he's become pope Francis like in his last few years and it's pretty awesome...
&gt;I'm starting to think the whole stock marked closed above blah blah blah is a pretty shitty indicator of how the economy appears to your average American. You are correct.  The state of the stock market is not the economy and the state of the economy...
And THAT is why we need public financing of elections. http://Wolf-pac.com'
This is the greatest thing I have ever read '
Eh I guess I could've been more specific. I meant it as it has nothing to do with measuring the economy ie good or bad. When I mentioned in the traditional sense of output I meant it as it will still remain on the right side of the equation. Either peopl...
The thing that really pisses me off is how the Demo party recoiled in terror like the bunch of spineless apologist pussies they are.No one stood strong said fuck y'all and championed the causes. They ducked dived and distanced themselves while they let t...
Just to add the stock market is full of irrational participants which makes using it to quantify the state of the economy a little suspect.'
It is one of several measurements we have used for years. People who think the only relevant metric to unemployment is a basic unemployment rate simply are not doing more than cursory research. Different factors give us different pictures of the economy ...
That would be insanely awesome. He'd need his anger translator Luther as a sidekick though.
Yeah everyone who doesn't like Obama is a fucking racist. GOD I hate that. I'm not Republican so your comment doesn't apply to me but I don't care at all for Obama. I do not support most of his policies and feel like he's talking down to most of the popu...
Do you have a link to the politician and bible verse quoted? I must have missed that. '
Central bank has no impact on long term prices. '
I don't know or care who nixonrichard is in the context of this conversation but what exactly is wrong with states rights?To my knowledge  that's sort of what the Founding Fathers had in mind in the beginning and while they couldn't have anticipated a lo...
could your sentence structure be any more convoluted?  I like to pretend I'm solving a rubix cube while reading reddit comments so your comment gave me a boner.dipshit.
. . . Marxist Leninist Stalinist Maoist . . . (I've actually heard him called all of those things).
&gt; Especially if you are here with an open mindhaha that's hilarious. I do believe you're barking up the wrong tree there my friend.
Okay here it is:There are legitimate and rational reasons for people to hate Obama.Here in /r/politics we focus on how Republican focus on Benghazi and things like that because we'd rather focus on how stupid Republicans are than focus on legitimate crit...
It's almost like some parts of wall street are fine and others are full of people who want to game the system for their own gain.
The market is propped up by low interest rates though.'
[deleted]'
Expected rate of return and nominal interest rates both have components of inflation. (Fisher equation etc.)That isn't what is happening here. The dollar has been strengthening a lot lately. We are currently in an up-trend now that the recession is over....
It was an opinion statement.  You expect a citation for the comfort level of /r/politics?'
You dutch are diseases. Edit: It was an econ joke. No offense intended. '
I'm speechless on how you can possibly put that on the strong dollar and fracking. Those must be the worst examples of factors driving an economy such as the US.Strong dollar means lots of imports instead of exports. Fracking means basically nothing but ...
But to be fair don't both sides do this constantly. Both sides point to the president for failures/successes (depending on the party and who's in office at the time) when in fact the president is usually not nearly as responsible as Congress for most stu...
What do you expect me to say?Republicans are ALSO wrong when they violate the War Powers Act.Did anything I said suggest otherwise?  There's just not a Republican running the Executive Branch right now.
Hey I heard that song too.'
Or the gold roads only got created by wealthy job creators *in spite* of Obama and his middle class crushing policies'
I wasn't cherry picking examples I was offering criticism.  Most of the things I criticized are ALSO things some people use to praise Obama.I wasn't responding to someone who was talking about reasons to love Obama.'
You're being a bit premature here. He still has a little over a year to destroy traditional marriage set up an American Caliphate and put all white Americans into slavery. 
underemployment rate is a much better metric(standing at over 12% currently). everyone is stating that the baby boomers retiring accounts for the labor participation rate but that doesn't fully account for enough of it.&gt;Had this trend happened while R...
We could start off with how they spent the country into oblivion during Obama's first term. Bush and Obama are the worst fiscal presidents this country has ever had. However the amount of debt that Obama has tacked on by himself has almost surpassed that...
I don't expect anyone to take ME seriously which is why I didn't say I don't like Obama.I listed specific pointed issues.  Whether or not you consider those issues serious enough to alter a vote -- whether or not you take me seriously -- those are very v...
Confidence in the economy does have a lot to do with what's going on in the stock market though and people aren't confident in the economy unless they're confident in the government.
[deleted]'
Because the issue of the particulars of how any individual votes is tangential to legitimate criticisms of a sitting President (who can't even be re-elected).
To be honest the reply is for everyone. I know this dude isn't the only one to belittle or misunderstand American politics. 
in america we pave the streets with oil even at $150/bbl'
Shut up and legalize it Obama'
Could you elaborate on this point please?'
Can you name one of those policies?'
Yes but I would imagine that she would die of grief.'
You sound just like the party you dislike.  '
Maybe but look where the money comes from.Dems get money from unions meaning that the workers are making money.Reps get money from business owner meaning the workers aint getting shit'
&gt; I'm not doneYou haven't even started.  You claimed the Democrats and Nancy Pelosi in particular caused the recession.You said:&gt; the economy did just fine under Bush until the democrats took control of the houseSo again what was it the Democrats d...
http://en.wikipedia.org/wiki/List_of_African-American_Republicans'
[deleted]'
You're forgetting that he started a war with Mexico under dubious circumstances to accomplish #4. A dubious war that ended with an army detachment seizing the Mexican capital and forcing a horribly one-sided treaty on a virtually powerless (by that point...
You said his presidency hasn't been a baby step to the left. That's simply not true. 
It's not only a year. See after he rips up the constitution and claims his 3rd 4th and 5th terms as President he'll have all the time in the world. Duh. 
Good on you it is refreshing to hear this sentiment. My perception is that Dick has been combative here from the 1st comment. The original comment he responded to was not about valid criticisms but that the **hatred** of Obama is a result of delusional t...
GOP politicians wanted to kill Obama?  When?'
You friend have won the coveted BEST POST OF THE DAY AWARD.'
That's really the shitty thing about any metric:  policy that more reflects the measurement as opposed to the overall quality of the system.
Ha no. I expect you to make ridiculous statements with zero evidence to support them. Looks like my expectations were met.'
I don't think the promise not to run for a second term was contingent on him fulfilling his other promises.  His party was having a lot of infighting so he thought if he promised to just serve 1 term they might rally behind him knowing they would have an...
Uh well he *was* the only other major party candidate at the time unless the Constitution Party is your bag.'
I want to see what fox news' Steve Docey is saying about the unemployment rant now after saying 6% sounds pretty good.
This is the lesson Elizabeth Warren and Bernie Sanders are teaching the rest of the Democratic party.You can be an aggressive progressive and it's an asset that'll help you win not a detraction that'll scare off the electorate.Over time we'll see more tr...
&gt; Terrible for civil libertiesCompared to an ideal President yeah.  Compared to most past POTUS?  I don't know if I would say terrible.
He was answering critics who said the stock market would go down.'
&gt; Obama is currently trying to get a new war powers act passedMmm-kay not sure how that is relevant to him violating the one on the books.&gt;indefinite detention is backed by congress by not providing funds to close gitmo;No it's not.  Obama was simp...
#Manifest Destiny /r/MURICA '
Too bad he lied about tons of other stuff'
Timothy Geithner.That is all.'
[deleted]'
Well yes I suppose? The comment I responded to suggested that Obama was doing nothing. So even if he was rounding up Jews for a second Holocaust he'd be doing something and that comment would be incorrect. As for your claim that Obama's actions (in the f...
Golden streets conduct traffic better!'
He doesn't believe in God but if he did it would be Allah.
You are correct. Anyone in office would be dealing with this issue.'
[deleted]'
Thats the kind of mentality that lets them get away with this fucking shit.  But if it was the GOP it would be worse!! No shut the fuck up its not about being better than the other guy its about being better for our country.  So stop with that terrible t...
Who cares? The political climate trended towards Obama he won the presidency his administration has been in power for some time. It doesn't matter at this point what the alternative was. I don't need to play this political race-to-the-bottom game and adv...
They got two branches of government out of it and might have a 3rd in 2016.'
Quantitive easing bye. '
I don't think he really wants us to run all the tapes back. Just the one on the economy. Not the one on transparency in Washington. We can forget that tape...
Ha probably. Karl Rove and all his ilk that the GOP/Fox news has created has waged the most successful propaganda war I've ever seen. It's like religion. Your crazy grandma is a kooky baptist who believes dancing is a sin? Well Mr. Grandson might have th...
It doesn't really. But republicans will argue that Obama is bad for corps and therefor bad for the economy. The headline of dow over 18000 is a good sound bite to say otherwise. Much how high unemployment has plagued Obama in mid terms. Sure it could hav...
Agreed. People follow GOP because they shout loud and clear not because they are right. They just *look* right to those who don't listen to both sides.
[deleted]'
This one? [Dan Pfeifferxe2x80x99s Exit Interview: How the White House Learned to Be Liberal](http://nymag.com/daily/intelligencer/2015/03/dan-pfeiffer-exit-interview.html)This is one of the best pieces I've read on understanding the Obama Administration'...
You don't get it. Obama tried to destroy this great country but the brave and selfless republican majorities stymied him and THEY sowed the seeds of all the good stuff that has happened. /s
[deleted]'
Sure it's not a post-apocalyptic wasteland as I predicted... but we would've had a utopian paradise if Obama hadn't destroyed the hopes and dreams of the country by forcing us into socialist healthcare.
No.  &gt; executing a war in violation of the war powers actAll Obama.&gt; for enshrining indefinite detention into lawFirst by executive order then by a law signed by Obama.&gt; for promising openness and transparency and then prosecuting a record numbe...
He secretly built the biggest domestic spy system in history.   It's probably illegal.    Chased the one whistle-blower to Russia.  I'd call that terrible.   
For real secret FEMA internment camps take time to build. It *is* the government building them after all.'
Um I'll take Racism for $200 Alex.'
He's finally the Marxist Kenyan Muslim I voted for and donated to.
You're going to vote anything and think it'll get better?
My comment was loosely based on a similar statement made by Conor Friedersdorf.Maybe he's a libertarian ;)
[deleted]'
It often runs counter to the state of the economy. Better than predicted jobless claims? That means the economy's getting better as a whole but that means the Fed may raise rates soon so the stock market drops. 
I had a gay budgie who died of grief when his partner flew away. The reasoning checks out. '
Its sad that he couldn't have done this from the start and called out the Republicans for their lies the entire time but they would just say he was lying.
Don't question Obama on his decrees it makes you sound racist.
&gt;Tu quoque b-b-but-bush Iraq commands our armed forces'
TYT and Cenk are the shit. I want WolfPAC to succeed. '
Perhaps there is a suitable gold alloy for road paving '
Not only that but when you lie all the time and you force the other side to always be rebutting everything you make them look like partisan whiners who are *always* contradicting you just because.It's a lose/lose scenario for Democrats because the majori...
He could still be the Antichrist though. '
So are you saying the percentage granted during the Obama years is the same as the percentage granted during the Bush years?Because IIRC it went from like 75% average for Bush to 65% average for Obama.'
Someone is a little too enchanted with their fallacies. No tu quoque I attacked your positions directly thank you very much.And actually it's b-b-but the last 44 presidents!For a fan of fallacies you sure do jump to a reductio ad absurdum pretty quick. I...
I'm thinking more like income inequality rampant outsourcing corporate polluters mismanagement of natural resources and decimating funding for local and state education which I guess the congress has some responsibility but at the end of the day there ar...
Can we re roll where he said that he would have accountability and transparency with his administration?'
*Happy Days Are Here Again**The Skies Above Are Clear Again**Let us sing like 2007**Happy Days are Here Again!*'
&gt;No tu quoque I attacked your positions directly thank you very much.Oh really?:&gt; Yet another thing that has been done by every President everInstead of arguing merits you were pointing to who else did it.  That's classic tu quoque.  
That's great. He also took the most money from Wall Street donations than any candidate ever. And surprise Wall Street had no penalties. Oh and it's been 7 years about him trying to reform capital gain. Nada.Close loopholes for tax off-shore havens corpo...
I've felt all along that (in addition to the above) their complete refusal to be helpful and utter ineptness at governing is actually a carefully calculated strategy: it's so much easier to weaken government (and it's regulatory power) if you make everyo...
no i think obama has been a sneaky shit president. however it would be dishonest and a disservice to the hardworking members of the bush administration to disagree that they really sunk that bar with the war of aggression against iraq no child left behin...
He could run for a third term actually. In all seriousness who would stop him? Congress? The American people? LMAO.'
Too bad policy didnt have anything to do with the recovery. If the Fed had not have pumped money into the system to prop up the stock market (which it is still currently doing with its guidance every quarter and cheap borrowing costs) then it would have ...
Yes pumping the stock market full of cheap money equates to a strong economy....'
you forgot about that war thingy'
false dichotomy '
People on the right would be quick enough to blame him if the stock market *wasn't* doing well so.....
What about heat conduction though. Will a hot day mean my car is melting?'
Can confirm.Source: Southern Indiana'
So much irony.'
Tu quoque applies to the person making the argument being a hypocrite not the subject of the argument.Tu quoque would be asking since the law has never been enforced so the question is why you are so offended by it not being enforced *now*. As to the dir...
uh oh downvotes inbound'
Love what he says but I hate what he does_-_'
Frankly these days I don't think the President has enough power to enact real change without committing political suicide or possibly being flat out murdered. I consider Obama a great president because he exhibits professionalism with a certain suave. Hi...
its actually comical that people still believe in Obama'
Post from yesterday showing a Neo Nazi propagandist had some key mod powers and multiple accounts to get around a lot of rules some subs had to push his agenda that the Holocaust did not occur.'
Wow. I don't think I've ever read a reply from someone whose assumptions about me were so offbase and based on so little.That aside I was referring to how B.O. has moved aggressively against state and local governments that attempt to enforce immigration...
I mean in context of what you said; you seemed to have implied that *I* can't complain about executing a war in violation of the war powers act for enshrining indefinite detention into law for promising openness and transparency and then prosecuting a re...
I think a lot of the market's performance is explained by investor-class inflation. As income inequality increases things billionaires might want to buy (luxury homes priceless art stocks) go up in price simply because the availability of those things is...
This should be the most successful caliphate with local minorities joining their ranks.'
Well done. I think you would appreciate this quote by my grandfather. http://imgur.com/rsDt4va'
Fair enough.  But if you think Republicans are the only ones backed by corporate interests I believe you're wrong. They just might be more successful.
Questioning Obama about *anything* makes me sound racist to people who can't get past skin color. Shameful.
It's funny too because at this point all that would be needed to win over an extraordinarily large hunk of the typically republican voters is to not talk about Roe V Wade and stand up for gun owners.
I.e. being a leader not a follower'
Nah the Anti-Christ seemed like a way better candidate. He's supposed to bring 1000 years of peace that's pretty awesome! Not really sure why that makes him the bad guy the other dude made 2000 years of war 
[deleted]'
Double-secret reverse apartheid.  We'd never see it coming until it was too late.
Ile take things that would actually break the internet for $500 Alex. '
and be white or at least east asian but ideally white'
Was the civil rights act a revolution? Is the current expansion of gay marriage a revolution?'
The constitution...'
Let's ignore that this was accomplished by making investing in the stock market the only place to get any decent returns by distorting the market interest rates for savings.Let's ignore QE inflating the stock market. Let's ignore all those subsidies for ...
Actually Congress is less productive during recent years than ever. http://dailysignal.com/2014/12/30/turns-113th-congress-wasnt-least-productive/'
[He's also the subject of an awesome They Might Be Giants song!]( https://www.youtube.com/watch?v=H9SvJMZs5Rs):)
The ACA Obama's benchmark legislation was passed with the Dems controlled both Houses.Clinton and Reagan had opposing parties control Congress. FDR and Hoover had their own parties control them and FDR basically continued Hoover's policies yet people don...
&gt;[Recently each of these eminent economists was asked whether the unemployment rate was lower at the end of 2010 than it would have been without the stimulus bill. Of the 44 economists surveyed 37 responded yielding a healthy response rate of 84 perce...
If Obama cured cancer the GOP would complain about all the doctor's and pharmacy workers he put out of work.
&gt;[Recently each of these eminent economists was asked whether the unemployment rate was lower at the end of 2010 than it would have been without the stimulus bill. Of the 44 economists surveyed 37 responded yielding a healthy response rate of 84 perce...
Which is code for a Congress that can run roughshod over the parts of the country who disagree with you.'
I agree with the sentiment but what you're describing is no accident. The Democrats who distanced themselves from Obama were certainly weighing the consequences of this against standing with him and virtually assuring a defeat and therefore a loss of a s...
Numerous other developed countries don't need this. Denmark has similar financing rules to the US and doesn't have the corruption. Maybe it's something else entirely that is the reason corruption is occurring such as parliamentary systems creating more t...
romney kinda hung his own credibility by saying that if obama won the market would decline and if he won it would increase.first of all the market isn't driven by who's in office. more often than not the president who presides over any particularly good ...
No doubt but you don't go from 0 to 60 in 0 seconds.
[deleted]'
People need to roll back the tape to see how Obama lauded the transparency of the administration.  It's as easy for Republicans to pick him apart as it is for his supporters to be blinded by his bullshit. 
Droning the shit out of the Middle East and breeding new terrorists - not good IMO.'
If we're going to roll back to things being outlandishly wrong how about any of your campaign promises Mr President?
That's true to an extent but the context of their unwillingness to get behind his liberal program was that they were scared of losing seats in traditionally conservative areas.  It's hard to run on Obamacare in traditional red states.  Of course they los...
I'm not really sure if that would stop them from campaigning while in office though.  The only thing I can think of that would help that would be to prevent reelection.  Once you get in office do your job because you can't run again.
First what does a backlog in 2007-2008 have to do with Obama's policies? Second we did actually get some progressive legislation under Obama and a Democratic congress (with 0 Republican support). thirda that all stopped in 2010 when the Republicans took ...
To be fair the GOP of the Clinton years were a long time ago and most of us either aren't old enough to remember or have completely forgotten how absolutely disgusting they were tactically.One would think that after the Bush years they'd be more concilia...
http://thinkprogress.org/justice/2012/01/13/403911/kansas-gop-house-speaker-prays-that-obamas-children-be-fatherless-and-his-wife-a-widow/'
[True true.](https://www.youtube.com/watch?v=4ztuf_r3NfA)'
I know you meant that sarcastically but I upvoted you anyway. Obama has his flaws but he's been correct far more than he's been wrong. He's still the best PotUS in my lifetime and only behind FDR and (perhaps arguably) LBJ. Also both fantastic Presidents...
Was the struggle for civil rights in the 60s a revolution? Yes. And gay rights and equality didn't happen overnight. Vast amounts of change and upset to the status quo were necessary to set the stage for what laws and repeals are finally going through no...
labor force participation rate. unemployment and labor force participation rate of those under 26 years of age. quality of jobs.'
They're already trying to take credit. They claim that by just taking a majority in the house and senate that business owners are more confident and hiring more people. They claim that just their presence has fixed the economy.
I bet he would do a great job too.  I mean the guy has comedic timing.  Every time he's funny charming the knife twists in the collective conscience of the conservative trust brain.
When we have currency inflation the stock market is going to rise.If money is worth half what it used to be worth then the stock market should double.'
This is the classic of things are good give yourself a handjob and if not blame the last guy bullshit.'
sit down!'
I have my own issues with Obama that doesn't excuse the blatant disrespect for the office of the President.  It is shameful coming from members of congress.  You can disagree with Obama's policies while abhorring congress' treatment of him try it one day.
Can this sub jerk it any harder?'
actually I believe it was during the SOTU when the 'you lie' outburst came out.
http://thinkprogress.org/justice/2012/01/13/403911/kansas-gop-house-speaker-prays-that-obamas-children-be-fatherless-and-his-wife-a-widow/'
&gt; and be white or at least east asian but ideally white-shrugs- I don't really think a vast majority of republican voters are racist. Often they'll be libertarian or maybe just extremely pro-life. In any event I can't say I agree that he'd need to be ...
I'd watch it.
Lol I was being told the other day that Obama is behind the police / protester uprising and plans to declare martial law before his term ends so he can stay dictator forever. hahaha I was high so at the time I was like oh shit... '
Interesting thank you.'
Everyone on this site spends so much time bashing fat cat wallstreet traders and such then go on to act like their success is a barometer of Obama's success. Which is it is Reddit happy that Obama and Wallstreet are doing well or are they angry?'
You would know they exist if you ever left your parent's basement. 
Pointing out that the Republican party houses the racists and has made many racist statements and dogwhistles and hired white supremacists is racist yo.'
Printing money has made the stock market rise along with prices for everything else for 102 years. It's called inflation.
/r/politicalcirclejerk'
THANK YOU. THANK YOU. I am a progressive social democrat and its mind boggling how many liberals and progressives support him and defend him over things they'd be having aneurysms over if Bush did them.Every fucking time it's The alternative is much wors...
I just don't like the greed based system. Politicians run on money they get from legal bribes in the form of donations. Once they're elected their first priority is giving a reach around to the entities that gave them money then they start the process of...
RonPaulItsHappening.jpg'
Or Mexico!'
I'm saying that we would go to war but you need to act tough and hold nations accountable when they breach sovereign nation territory. Also sticking to your word is another good thing to do (Obama backed down on chemical weapons in Syria rhetoric). If yo...
I sit corrected.'
I disagree I think anti-black racism plays a huge role in the extreme disrespect that Obama is shown by some of his political opponents.  I think it's Bill Maher who said just because you're a Republican doesn't mean you're a racist but if you're a racis...
I don't get ticked off by that sort of stuff. I'm guilty of making generalized statements without researching what I've said fully because it just seems like the right answer. It's something I've been trying to work on in myself and honestly I love the p...
Spying on Americans blowing brown people to shit and corporate ass kissing is when the parties agree. '
It absolutely is bull. We can without a doubt say the economy is better then when Obama took office that is a fact. However there are many areas that still need major improvement. Many of the jobs created are low wage many are still on food stamps becaus...
[deleted]'
Mixed race is the new white. '
Thank you.  Maybe ticked was the wrong word. Just basically annoyed like you said people get away with generalized statements. '
http://en.wikipedia.org/wiki/Amash-Conyers_AmendmentBoehner and Obama teamed up to kill this one. '
\&gt;Making them fight each otherLet me tell you about a little group of people called the Shia and the Sunnis...'
Well pick your fallacy arguing that something it's a legitimate criticism/dislike because other people have done the same in the past is NOT addressing the merits of the criticism.
What specific policies that were signed into laws can you name those? '
Last numbers I can find is 39% denial. However that includes:&gt;instances when it couldn't find records a person refused to pay for copies or the request was determined to be improper under the law Where that number changes to 9% after.Have to be carefu...
&gt;It's frankly absurd that people still keep trying to blame Obama for that.[No blaming both him and Congress is reasonable considering he requested the provision](https://www.youtube.com/watch?v=4DNDHbT44cY&amp;noredirect=1)And even with a signing sta...
\#NOTMYPRESIDENT'
yes convoluted more it could be.'
I don't think racism has such a big role in it... I know because I used to hate Obama. I hated him because his political agenda was basically the opposite of what I wanted to see and I saw what kind of power he wielded. That being said my disliking him h...
At least we have a list of who they are.'
&gt;One of those insiders he appointed was for the FCC. The FCC that just gave us net neutrality and stopped ISPs from ruining our internet in a 3-2 vote with that appointee being the deciding factor. You realize that Wheeler opposed net neutrality at th...
Ha ha ha! That's right! We got Obamacare. The worst law ever to be passed. It is all Democrats can do is screw things up.
The AP did a big report on it:http://bigstory.ap.org/article/ab029d7c625149348143a51ff61175c6/us-sets-new-record-denying-censoring-government-filesThey seemed to suggest it was getting worse.  They also had this disturbing nugget:&gt;Under the president'...
&gt; Obama's war policy IMO is one of his great faults. I think the US has grown weaker over the years since Obama took office.War in 2015 is not what war in 1990 was.Not. Even. Close.Hell war in 2001 is very different. 
The Anti-Christ doesn't appear until after the rapture according to the bible.  So if he *is* the Anti-Christ nobody was taken.
Honestly James Polk is your best example? Seriously he was playing on easy mode compared to 20th/21st century presidents. Moreover four goals in four years is hardly something to write about. Modern presidents have way more on their plate and accomplish ...
Wait did she say the federal reserve is not ready to hike the interest rate that might be a sign that the economy is not doing as well as expected Uh... I'm just a dumbass tech guy who listen to NPR and the reasons for not hiking the interest rate right ...
Um Democrats are right wing too'
There goes 'fact checking' onto the Blacklist along with 'global warming' and 'Affordable Care Act' :(
Thats fine you simply must accept that you are getting nothing out of it by voting 3rd party.'
As a leftist there's a whole lot with which I'm dissatisfied with Obama. But being the centrist/elite/conservative that he is Obama had done remarkably well.Posterity will regard his presidency as successful. Despite absolute unconditional opposition fro...
Well done sir...well done.'
THE WHITE MAN MARCHES ON.'
I did not excuse nor agree with the obstructive approach many republican congressmen take with him.I am not anti-Obama. I am happy he got a second term. As much as I was disappointed with him and myself for truly believe his change in 2008. '
&gt;Obama is currently trying to get a new war powers act passedI have not heard of this effort but if he wants something stricter than what we have now why couldn't he follow the current rules?&gt; indefinite detention is backed by congress[And Barry O]...
And who could blame them? The fact that they've accomplished as much as they have considered the obstacles the open political hostility feckless Democratic party leadership and the constant deluge of right wing noise - it's a miracle any president could ...
You have to trolling.'
You said:&gt;Too bad policy didnt have anything to do with the recovery.I give you an example of one that has and you move the goalpost.  But speaking of [U6](http://portalseven.com/employment/unemployment_rate_u6.jsp)...'
&gt;8chanare you fucking high? did you really just compare 8chan to stormfront?all people do on 8chan is talk about video games and post jailbait. wtf does racism have to do with anything?'
I'm more interested in a case of them being able to **pass** more legislation together.'
[deleted]'
As someone who grew up in a conservative suburb I can promise you it's also a lot about religion.
If there was naivete it is the kind of naivete that is unique to the office of the POTUSA; no job in this country prepares you for the rigors of that office. There is a learning curve involved for anyone elevated to that station be they businessman gener...
Well given the brevity and tone of your comment I don't think I formed any unreasonable impressions. And really one would hope that incorrect assumptions would be based on a little information rather than a lot don't you think? Anyway what you're describ...
http://thinkprogress.org/economy/2014/12/10/3601742/cromnibus-lowlights/'
You could pretty much condense all that down to the GOP and their base didn't like that he was black'
No. He just had marketing.'
There are definitely people who oppose Obama purely on ideological grounds but there's also a big part of the Republican base that is unable to respect a black man and more damningly the Republican party doesn't do a whole lot to discourage this or dista...
The Democrats follow the money. We're not going to see some new progressive era. Not without class struggle.
Its gonna be a whirlwind of an 18 month stretch. '
FEMA internment camps for gun owners was an actual thing the tea party was worried about though.'
jesus christ. '
So they passed a mandatory government funding bill? That's what more legislation together than /r/politics is comfortable admitting is supposed to be?Fairly low fkn standards if you ask me.'
&gt; Under the president's instructions the U.S. should not withhold or censor government files merely because they might be embarrassing but federal employees last year regularly misapplied the law.I think that really just speaks for itself. Same reason...
Dems don't need a prominent left wing. Pandering to the crazy right wing is what's killing the republicans. Legitimate republicans need to play to the anti-gay anti-intellectual crazy sect of the republican party in order to get elected so the sensible c...
I had a very similar though a few weeks ago. We have all these Republicans that run their mouth and their popularity sky rockets and get a shit lot of tv time. And I was thinking the Dems needs to take to this strategy. '
[deleted]'
I thought most east Asians voted Democrat '
Thanks. I totally understand your point. '
The GOP follows it a lot more and more obediently. The Dems at least live in the real world with regards to economics science foreign policy etc..'
So once you figure out how to do your job you're gone? imagine if we fired waiters once they got 4 years of experience. There'd be a lot of shit waiters in the world.
Yes he has done very well. The irony is for how much the right bashes him he's been very good to them.I am not anti-Obama. The idealist in me disappointed in him. He could have done more especially in the beginning when he had majority. Realist in me kno...
Don't forget he still has to take our guns with his jackboot thugs and false flag crisis actors.
Typical reddit left wing circle jerk incoming...'
We also have the lowest unemployment rate in a while and that's something everyone can be happy about!
Hi `orlrdvxl`. Thank you for participating in /r/Politics. However [your comment](https://www.reddit.com/r/politics/comments/2zsw32/obama_on_gop_critics_run_the_tape_back_president/cpm6r82) has been removed for the following reason(s):* Your comment does...
Which the Republicans would love to do themselves.'
The establishment Dems were so afraid to lose the middle that they didn't stand by a popular President with great numbers and it cost them Congress. When the right says things like he's the worst President since Carter and voters don't hear the left call...
&gt; declare martial law before his term ends so he can stay dictator forever.That was my exact fear with Bush after the PATRIOT Act was passed.  All we needed was one act of terrorism on American soil loosely linked to some Muslim extremist group and we...
They're both bad. One is more open about it than the other. That doesn't negate my point though. You're *not* going to see some new new deal come out of the Dems. Most of them are to the right of Nixon these days and they're only going to get more to the...
Yeah I heard he's trying to take over the military for his own purposes. I guess being commander-in-chief isn't enough power for him.
When 1/3 of your rejections turn out to be erroneous that's more than just blaming a handful of bad apples.  That's gross mismanagement.  
I love that. Thank you for sharing. That is what I try to do. Don't always succeed in that approach. That is my goal though. Your grandfather is a smart man.
I can see the Repugxe2x80x99s ambiguously gay chorusxe2x80x99s xe2x80x9cwhine alongxe2x80x9d is singing again here in /r/politics. Whaa Whaa.  The video showed Repug leadership making end of the world doom and gloom predictions about Obamaxe2x80x99s poli...
&gt;He's saying that it's healthyHe says this because he knows his constituency doesn't know any better. The markets and the parabolic rise in the dollar are driven by currency wars which have [nothing to do with the regular economy.](http://www.bloomber...
Yes both sides would love to exclude opinions contrary to their own and insulate the policies from scrutiny because it was done democratically.'
Oh this argument makes me crazy. The economy does not magically right itself. Policy has a dramatic effect on the economy and this economic cycles theory only gets toted out when right wingers try and discredit liberal economic policies. '
And what of the glare?!'
I want to agree with you but I work in an environment where idiots think driving a fuel truck to a site mid wildfire is an expected course of action so it's not hard to actually understand how bad those applications probably really are.
Now let's run the tape back on promises of transparency. He had fuck all to do with the economy just like any politician. Keep drinking the Kool-Aid.
&gt;jesus christ. Would not approve of such treason. '
Always a classy move by a president to slip in the old I told you so.'
It probably depends on where you live. I've lived in Chicago indy dc and Phoenix suburbs maybe it's different in the south. Sure we all go to church but our politics are all about money/economy religion isn't a factor in our politics. For example most pe...
Yes but it's is the Pity Party that throws insults and hissy fits against Wall Street so to quote the Dow is just pathetic.  So enjoy your hypocrisy.  Pity victimhood and  hypocrisy after all  are the mainstays of your party. You can try to have everythi...
LoLhttps://www.youtube.com/watch?v=haQzdW7hg4A'
[deleted]'
Which reminds me of the hyper-inflation scare that resulted from quantitative easing.Why does anyone still listen to these charlatans?  They keep saying they sky is falling and no chicken little [it's not](http://www.marketwatch.com/investing/index/dxy)....
Because I own so many stocks... get real.'
As someone who chooses not to get involved in politics. But my family is a bunch of raging obama haters. Can i get a recap of the pro/cons of his admnistration of the past 8~ years.'
Eh.  I'm not saying we're going to see inflation or not but there is a metric shitload of money sitting on the sidelines not being lent out because it's safer to earn .5% on money borrowed at 0%.  Also the 5 year version of the chart you linked is fuckin...
Wow.First the economic collapse was caused by Republican overspending.Second see first.Third the Republicans haven't cut the deficit through reductions in spending.  The reductions have come from ending the wars.'
I'm not sure if you realize but it is reaaaally creepy the parallels between how you talk and how the wackos in the GOP talk. 'Mittens' and 'Obongo' caricature names followed by baseless fear mongering. Look in a mirror dude fucking Christ.
Wow. I let out an audible gasp at the thought of how awesome that would be.'
The constitution is Obama's asswipe if you hadn't noticed.
[deleted]'
&gt; No shut the fuck upHow you lose all credibility and respect when making a point.'
&gt; According to a story told decades later by George BancroftBut that's not what he said during his campaign.&gt; [The northern boundary of Oregon was the latitude line of 54 degrees 40 minutes. Fifty-four forty or fight! was the popular slogan that le...
Yet people on the right were happy to predict the Dow down at 4000 if Obama was elected or re-elected.  It's not about Obama did that it's about noticing every time people like Limbaugh or Dinesh D'Souza make a prediction you can make a 100% guaranteed b...
real quick'
Honest question but after a little research the deficit did increase from 2009-2012 substantially.  Why was that?  Obama was in office during that time.  Was it because of plans Bush put in place?  (Got my info from this site - http://www.usgovernmentspe...
[deleted]'
I had a coworker that is a staunch creationist republican and legitimately tried to ask me why black people don't vote more for republicans since black people are usually so religious and so are  republicans. I said well republicans are racist so there's...
Listen to the Democrats   Wall Street is evil. The 1% are getting richer while the middle class languishes. The rich are getting richer because of Wall Street closing above 18000 which the Democrats claim was due to their policies. So they take credit fo...
Russia wasn't scared of Bush when they fought Georgia.  And we have fleets sitting in the South China Sea Detering any move by China
Unemployment is a shit number. Try employment to population Ratiohttp://data.bls.gov/timeseries/LNS12300000'
I'd read a tell all book written by Obama. 
I honestly thought bush 2 was going to burn another reichtag and do this. '
&gt; James K. Polk filled all his campaign desires in his first termSo the US border with Canada is at Fifty-Four Forty?'
The Syria deal was a much better alternative because we got the weapons out of Syria w/o having to bomb the shit out of them and we worked with Russia on that deal.  If anything our threat forced Russia to do something.  I don't think Putin is trying to ...
By all means what actions have he performed that the Supreme Court found unconstitutional? If its one thing I've learned people who believe  constitutional overreach seem to misrepresent the constitution itself for example treating the first amendment as...
Man that thought is a happy one.'
Yeah I have a feeling he will be break records with his book sales.'
Oh noes pornography! Masturbation! Much terrible very shame'
Uhh... Dick Fucking Cheney.Enough said.Or maybe more appropriate for you would be Ehrlichman or Liddy?Politics at any level is a shit sandwich *at best*.Geithner is insider bullshit no question but have you had your head in the sand since forever or what...
We can't ever put them on trial because they would spend the whole trial talking about how they were tortured and the previous administration violated the Geneva convention and most/all of the evidence against them was illegally obtained.  We can't let t...
Why can't he get re elected?
Convinced is the wrong word I think enabled is better. '
Probably one of the few times I will agree in this regard both parties are the same but the Republicans are louder'
Nono orange is the new black- not white.'
Andrew Jackson.Admittedly he only had 4 promises and 3/4ths of them were dickish but still'
Not to mention that it's also why they're so eager to make it easier for unlimited amounts of money to be available for political ads in all media.
Yes he was fatherless and his (secret) wife was made a widow.'
I was gonna say the libs said the same about bush and he never declared martial law. People from one side always worry when the other side is in power. The things said about bush were just as bad as the things said about obama and with bush many of those...
That he pushed against the will of a lot of his party-there were plenty of things in the bill that made some Democrats vote against it. Have you heard of the TPP lately? Or the bank bailout or the NDAA? There's plenty of things they've worked hand in han...
Saying shut the fuck up does not have any effect on ones credibility.  Yes it loses respect and yes people will stop reading after that but I've been seeing this same exact comment on these threads since Obama took office. It's like saying Yes Brian kill...
Govt worker can confirm The camps we are building won't be finished by the time Obamas done.
Touchxc3xa9 '
Obviously yes. It's not even 2016 yet. 
What are you going to do about student loans Obama? That ish isn't going to pay itself. The cost of education is too damn high. (Sarcasm) for the lame man
Ten years from now there will be another Democratic President and the Republicans will claim that he/she is unwilling to compromise. That they wish they could go back to the days of Obama when he would work with Congress.Don't believe me... Well the did ...
word'
Most people don't understand how quantitative easing hurts the economy by creating a stock market bubble that will eventually burst destroying the value of the dollar.  Essentially destroying the middle class so that the divide between the haves and have...
You never know what the government is going to do. Crazier things have happened. It just happens to OTHER people haha'
[deleted]'
&gt; They claimed he would increase the deficitTo be fair he would have given the chance. The claim that this would be a detriment to our economic recovery is what I would dispute (at least at the point in time they were arguing for smaller deficits).'
Headline unemployment rate is such a farce.  Haven't you seen the daily Bernie Sanders post on the front page about that?
Did you miss the whole SAE fiasco?'
You HAVE to talk down to most of the population. He's educated not every other citizen has a higher education. If he used big words it would become fodder for those who equate education w/ elitism &amp; other bad things.'
Jesus christ you act like if Obama hadn't been the miracle worker that republicans required him to be. That this place and beyond wouldn't be lit up with the same thing from every republican on earth. So yeah I'm perfectly okay with it. Even after everyt...
What's the Republican plan for student loans?  
Wait are you still buying that GOP BS about how we should be afraid of China?'
I think a lot of those relied on people helping him'
[deleted]'
What is a dictonomy ?'
Eloquently put. I believe you're correct and I'll confess it's a satisfying thought how bonkers he must drive those hateful square old dicks with his inescapably smooth demeanor.
&gt;Most of them are to the right of Nixon these days and they're only going to get more to the right down the road.Here's where you're wrong. History tends to be cyclical.In the late 1800s the government was overwhelmingly (what we would now call) conse...
The title alone from your first link says consumers aren't spending even in a booming job market so there's some more good news and yet another fact that goes against predictions made by Obama's opponents. The point again is that Obama's presidency hasn'...
Now that the Republicans are in  charge what is their plan to fix this?'
The same goes for gas prices. While potus has very little if any impact on the national gas prices the fact is when gas prices are high GOP has blamed the pres when gas prices are low they are like well the president doesn't impact gas prices'
...by using their own guns against them that he successfully took after 7 1/2 years of trying.'
Yeah that shit's going to trickle down any day now.......
so let me get this straight. wall st is over 18k...which only means that the 'rich' has gotten 'richer'. why is this such a good thing redditors? main street investors have left the market YEARS ago. if you look into the unemployment numbers you'll find ...
I can see how you'd come to such conclusions when you start from inaccurate premises. Your inability to use logic is why no one can trust conservatives and is one of many reasons not to do business with you whatever that may mean.'
And what have the Republicans done to help?'
The things said about Bush aren't even close to the things said about the current president. The level of disrespect toward this president is unprecedented.
He's the most liberal president we've had since Carter.  I grant you he may be too moderate for some but he's been better on progressive issues than the last FOUR presidents.  I think that's something.
I don't care. I thought we already established that.
not a single thing in your list is what the GOP and their right-wing base is criticizing him for...thats the whole point of this post'
&gt; its not about being better than the other guy its about being better for our country.What's better for our country?  A drone strike in Yemen that kills 10 innocent civilians or a full scale invasion of Yemen that kills thousands of civilians?Because...
As calling them decrees makes a person sound ignorant.'
Thats the problem. Neither have. Wake up. '
What was the alternative? If not QE and low short rates then what else to stimulate the economy? Fiscal stimulus? One of the reasons monetary policy was so dovish was because fiscal stimulus was lacking and still is. And we all know who is responsible fo...
&gt;an atheist and a muslim.  oh no! He'll corrupt the country with his non-Christian beliefs!
Argh you're right I got it backwards. That's what I get for posting right after a 60 mile bike ride. 
I didn't say its a good idea.  But its probably the only way to prevent them from campaigning on the job.  We could make it outright illegal but I doubt that would stop them.
Because everyone knows it's stock market and banks that counts so let's spend another $8 trillion ($8000000000000.00) helping banksters...
Well to some degree I think the economy does right itself. But yeah it won't do so without the proper policy in place. '
Give it time. How will the world reflect back on a president who handled 9/11. Prevented an economic collapse because of Clinton aggressively pushing Americans to become homeowners. The Patriot Act was a must when we discovered that enemies were among us...
If they didn't succeed in the election then they would say that their blocking of Obama is what saved the economy. 
Exactly how has his foreign policy been worse for out country in your opinion?  Please be specific.'
The problem is not realizing that the economic slump was a *correction* from a bubble bursting. Furthering distorting the economy simply makes thing seem nicer than they necessarily are.'
What did I say? this is not a repub/dem thing. GWB started the nonsense with the Iraq war...no child left behind and the medicare part d which increased spending ok? then barry took that and went nuts on bailing out the banks...tarp....the car industry.....
Look for yourself. http://en.wikipedia.org/wiki/Historical_rankings_of_Presidents_of_the_United_StatesIf you look at the collapse in the American foreign policy that had held steady since WWII through Democratic and Republican administrations being compl...
Tuoche.'
30th time's a charm!
Yup. That's exactly what's happening.#/s
Why are the DJIA and S&amp;P 500 the barometers of our economic well-being? We use the stock market to measure our wealth and then we wonder why rich bankers are getting richer while inequality is getting worse.'
If the Democrats stopped banging on the anti gun drum I think that it would go a long way in not automatically giving the rural vote to the Republicans. IMO'
[deleted]'
PREACH IT'
If you're going to take credit for the success of something you had better be ready to take the blame for it's failure.
I still don't like him...but lordy do I hate most of the people who hate him.
The phrase you are looking  for is:It is hard to not paint everyone with the same brush when they stand so closely together'
The stock market respects no person or political parties.  You could have the best president ever with a horrible stock market and vice versa.  It's going to do what it does and who is president will have little to do with it.
[so close.](https://www.youtube.com/watch?v=95KTrtzOY-g)'
I know this is selfish but part of me is glad that the GOP made such fools of themselves by spewing so much nonsense. I'd imagine that there is a much larger young voting base because of it and a lot more people who will vote democrat in the next electio...
Dont wiki me.'
Same as always nothing.'
&gt; the Left would like him to do different thingsHell what can he do without congress support. Answer: not a lot.'
most of the people that dont like obama care more about the economy and always talk about how shit hes been with that'
Yeah and the Democrat/Obamabots will have a monopoly with no Paulbots around. '
I question Obama all the time and have yet to be called a racist as a result. (I did so in this very thread as a matter of fact.) I think that avoiding accusations of racism when it comes to criticizing Obama will depend on the criticisms. Obama has had ...
I don't think that quite applies there's still a massive gulf between the racism that exists within the Democratic party and the racism that the Republican party relies on to stay politically relevant.
Yeah which his kept afloat by the feds low interest rate policy of quantitative easing. Watch every time the Fed even talks about raising  rates the market tanks.'
Here's a good list of things he done. http://whatthefuckhasobamadonesofar.com
A lot of people have floated the idea of limiting the campaign season to just a few months. '
Okay I believe you but my tommy gun don't
I've kinda gotten away from TYT. I like a lot of what they have to say on issues but what I don't like really grates at me. Public financing of elections? Yeah I'm totally down. But some of the other stuff? No thanks. I haven't watched their political pr...
I don't care.
So you're suggesting the Fed shouldn't have done anything and let the correction run its course? I'm curious to hear how you would've seen that panning out.
From what I've seen republicans hate wages in general doesn't matter if it's war or Wal-Mart
[deleted]'
What do you base this off of? Not saying you're wrong. I'm not American I'm Canadian and our news covers your politics quite a bit. Literally the only times I've heard about Sanders and Warren were on reddit and reddit is hardly the deciding factor in a ...
[deleted]'
Oh god damn it why did it have to be someone from kansas. We already have Brownback ISN'T THAT PUNISHMENT IN ITSELF!?
&gt;No blaming both him and Congress is reasonable considering he requested the provisionHe didn't really have any choice and everyone knew that.  Suddenly cut off funding to the military in the middle of a war was never really an option.You have to unde...
The President of the United States is the commander-in-chief of the military. It is literally the job of the President to oversee the waging and winning of wars. '
Oh man my local news is in northwest Florida and anytime Sanders or Warren do something they feature it in their national news (unless something like a natural disaster happened then it gets top billing).They're also all over my Facebook but that's a pro...
it's stimulating the economy. think of all the booze drugs and  ~~sweaty glistinin' nekkid young boys~~ whores they're spending money on to ease the stress...
http://en.wikipedia.org/wiki/National_Defense_Authorization_Act_for_Fiscal_Year_2012The NDAA is the bill every year that funds the military; the 2012 one was the one that contained the indefinite detention provision.Here's a link to the signing statement...
But the video I presented to you clearly showed that he wasn't bullied into signing the provision. He requested it-I could understand your argument in holding up the bill if he actually fought against the provision but he didn't. The President still does...
Guantanamo is a separate issue; I assumed you were talking about the provision in the NDAA.But for the record yes I very much do blame Congress for keeping Guantanamo open.  Obama has been trying to close it since his first year in office and has been bl...
The point is that everything you pointed out was started by Bush et al.if the GOP attacks those things they will very quickly see the fingers pointed back at them. '
[deleted]'
I have a few republican friends-- one of them who is otherwise a very stable rational person believes that Obama will not step down.  He truly believes this. So I made him a bet that when Obama is no longer the president will he look back and re-evaluate...
Daaaamnnnn you're a beast.
That's because they don't have any money to be taken away from them yet.
History is not cyclical. History is dialectical. More to the point though I think you're wrong to place all your eggs in the progressive liberal basket. The Democrats like to hijack movements when they become powerful enough to seem threatening. They don...
The wall Street donations are by employees of all of the wall Street companies. Secretaries office assistants lawyers anyone employed by those companies (which is a lot of companies in its own with a lot of employees). These individual donations were reg...
You know who brings up racism?Racists'
&gt;who handled 9/11.Poorly by declaring an undefined war on concept and going to war with an uninvolved country under a false pretense.&gt; Prevented an economic collapse But then caused his own?&gt;The Patriot Act was a must when we discovered that ene...
That quote is misleading.  The President wanted them to remove all language in the bill talking about indefinite detention.  They changed it a little bit after that but only removed part of what he wanted them to remove.The original bill before his veto ...
I disagree. He was definitely naive. IMHO he honestly thought in the fight for the ACA that there could be a reasonable bipartisan back and forth and a middle ground was to be had. They screwed with him and never changed one vote. Remind me again how man...
He might even squeeze in time to take away all the guns.'
Obama has single handedly added more to the national debt than all other past presidents combined.'
What? Unemployment rate is the rate of people actively in the workforce unable to find a job. This ratio measures the whole population (including people not actively seeking jobs) vs people employed. Pretty sure unemployment is a better statistic to use ...
And Hitler. Remember all the Hitler mustaches...'
Not really. Source : I've met those rednecks. 
Not if people give up looking'
[deleted]'
I just want politicians who *want* to be statesmen.'
True. There are strengths and weaknesses for using both statistics and it depends on how many people have given up looking for jobs and how many people were not looking for one in the first place. Given that this data shows all people above the age of 16...
I don't want them to disappear because we'd end up with SJWs and insane leftists ruining our lives  instead. We need a balance so moderate reasonable minds can run the country as efficiently as possible while still managing to placate the masses and stop...
The opposition does not want to argue about these tools because they want to be able to use them when they return to power. '
[deleted]'
You realize this is stupid right? '
That's who was voted for the first time it would have been nice if he had delivered it for the majority of his time in office.
[deleted]'
I'd say this is more of an experiment on their part.  If they don't actually garner any votes outside of their own local districts then the experiment will have failed and dems will continue to be mealy-mouthed center-right baby-cons.  It's up to us to s...
My primary fear about Romney being elected is that the guy believes in magic undergarments that can stop bullets.'
No Hillary is going to put him on the Supreme Court when Ginsburg retires.'
I feel like for a significant proportion of those voters you also have to be an old male WASP with an -R after your name regardless of your stance (which the old WASP with the -R after his name already supports of course).'
I have met my fair (maybe more than fair) share of lost cause rednecks but I have also met quite a few gun owner/enthusiasts who really don't like the Republicans but vote that way more or less because they view the Democrats as hostile to their way of l...
Given how the 1920-21 Depression was handled in a similar way and quickly resolved itself perhaps we would have recovered faster or at least the growth that followed would have been more real.'
&gt; Unemployment rate is the rate of people actively in the workforce unable to find a job~~Unemployment only measures people who are in the unemployment system.  Once benefits stop and unemployment runs out they are no longer considered part o the unem...
gosh... I do love some good old fashioned bigotry.'
But Polk didn't accomplish his campaign goals.  While decades later it was said that one of his goals was Acquire some or all of Oregon Country but he campaigned on getting all of Oregon Country.  Which he did not accomplish.'
But aren't you doing the same?Polk campaigned on getting all of Oregon country.  He didn't deliver.Maybe some adviser will say decades later that Obama's goals were to end the recession get unemployment under 6% pass healthcare legislation and get combat...
The 20-21 depression is a lot more complex than that.  WWI had a bit to do with it.'
Lol...you are naive.  '
Nah you have every right to complain about that stuff and to use your vote to express it.  It's Congress who are being two-faced.
So if Obama can't give a prison a fair trial you find it acceptable that Obama's solution is to just keep them locked in a cage?Last I checked if you can't give someone a fair trial you let them go free.
But the republicans in the video?  Were they wrong?  They were wrong weren't they?
The Republicans in the video - were they wrong or right?'
I'm the same age and its amazing to me running into incredibly opinionated young people that are quite obviously regurgitating the hatred their parents spew forth. It's makes sad some days and I always pity them. It makes me think the whole boomers final...
The Republican predictions in the video used them.They must be stupid.'
It could* be worse. What I'm trying to say is that when someone says the gop would have been worse isn't sddressing the issue but writing it off. This is why the two party system is fucked Becaus people (like you) are becoming angrier about someone who i...
Do not whatever you do  join a debate team.  Your liberal tactics  deceive demonize divide and destroy don't work on people who understand more than the headlines. You don't want me to include unintelligent to the Democratic  the pity &amp; victim Party ...
You said rural Republicans. The Democrats have little chance winning over that group. You seem smart enough to have figured out the Democrats aren't coming for your guns. 
Considering that the evidence for invading Iraq was fabricated I think it was a very real concern.'
The issue here is the framing of statistics'
[deleted]'
My grandma still says he's trying to take over this country '
The same could be said of the Great Depression. However when you define increasing GDP as growth but also define government spending as necessarily increasing GDP also while disregarding business to business transactions as part of GDP you get two things...
&gt;Reality: deficits cut by 2/3rdsWhen did we start running a surplus? National Debt clock seems to think otherwise. As someone who generally disagrees with Republicans the national debt is hard to argue. That said they wouldn't do any better... it'd ju...
Obama fulfills Romney's campaign promise on unemployment in half the time but of course it still isn't enough.
Maybe he should do something awesome. Maybe he should live up to some of his promises. Really push hard against conservatives. Get them all freaked out and unable to pass their own agenda. '
Obama has not received near the hostility that Bush did. I'm no fan of Bush but every media outlet from print to video treated him much worse than Obama gets treated Fox news excepted of course.
I gotta be real. That Susan is a fucking cunt sitting over there trying to use cheap fallacies to trick Americans into believing that BS. That's the kind of shit that pisses me off about American politics.Edit: Sorry if this isn't civil enough I'm just h...
Thanks for the link. A good article.'
Be nice to commas and they will be nice too.'
Worst of all many of we the people will cheer it. It's not just power hungry politicos normal folk love to see their views forced on other people. (And it's gray some views even the most hardcore live-and-let-live person would agree to forcing on others ...
Bush was yapping about how great the markets were doing in 2006/2007 before the recession. Methinks it's time to be careful about any stocks you own...When presidents talk like this usually a recession is a year or two away. 
I think it's *exactly* the you HAVE to talk down to them attitude I was referring to but it's not a matter of his using small words it's an attitude of I/my people know better than you do and yo have to think this way...'
Sure.  But using a bubble as a life raft isn't necessarily a bad idea.  Especially if they let the air out slowly at the end.  Might make the recovery take a little longer but it sure beats a crash landing. 
You know I used to be a lot more sure that the Democrats weren't coming for my guns.  But for the first time I was happy that the Republicans controlled the House when Sandy Hook happened.  I mean have you looked at the laws that were passed directly aft...
Dems get very little money from unions. They get their money from Wall St. just like the Republicans.  '
Yeah Obama doesn't play with his toys at the NSA. SOOO weird he won't mister snowden play...:/
I think we'll have to agree to disagree on the numbered points.  As for And really one would hope that incorrect assumptions would be based on a little information rather than a lot don't you think I'd think personal assumptions/attacks on others would b...
As a politician you reeaallly don't want to be inviting people to run the tape back because while they may find something that proves you right they will inevitably find instances where *you* were full of shit too.'
[deleted]'
1.Libya--not his fault..congress wouldn't even bring it to a vote...get off yer asses and do something2.enshrining indefinite detention--sucks..but unanimous on the republican side..while majority support by democrats..a conservative bringing that up is ...
Yeah he'll do wonders for the ratings because he's so hilarious.
How does that even work?  Who can give up looking?'
Wow Psalm 109... that kind of messes with the far right's assertion that the Koran is a book of violence and the Bible is all good.
Yes please.'
I've been accused of using too many commas as if my last name was Shatner
&gt;1.Libya--not his fault..congress wouldn't even bring it to a voteWhat a delightfully despotic way of looking at things (the President HAD to break the law to go to war because the Congress wouldn't authorize it!).&gt;2.enshrining indefinite detention...
Deficit cut.  Debt is a separate issue but was mostly run up by Republican presidents.'
Thank you. That's fine. But on reddit we can only know each other based on the words people use here. I made two assumptions about you based on your comment. One of them was that you probably never referred to executive orders made by Bush as decrees. Th...
You're measuring it wrong... measure from the base'
Republican Jesus would. '
You forgot Nazi Marxist.'
None of those took your guns away. The things that will most affect you being controlled by the party of stupid is the last thing you really want but vote against your own interests if you feel so inclined. We get the government we deserve. Rural Republi...
The problem is the 'foam at the mouth' hatred for Obama was so venomous it just made the center-left (like me) defend him more and focus less on real criticism because I couldn't stand to admit those psycho's might about any of it.
Good article overall but one big qualm for me is when Pfeiffer says they learned *quickly* that they couldn't change the polarization in Washington. That's dishonest. It took them basically a full term maybe even a full term-and-a-half. They should have ...
Not sure if I referred to Bush's orders (executive orders or administrative policies) as decrees or not -- I likely did. I am SURE I used much strong words for some of Bush's sh*t though.And yes I actually do consider myself a constitutional scholar.  Ho...
[deleted]'
Well they are trying to run some kind of military trial on several of the prisoners right now although it's taking forever.  If it had been in a standard court it would have been done by now.But no I don't think it's acceptable to hold anyone indefinitel...
I thought this thread was about the post at the top of the thread.  The ones where the Republicans were wrong and the President was right?'
So?Who sets these metrics?'
[deleted]'
&gt; When we have currency inflation the stock market is going to rise.You sir have NO IDEA what you're talking about.
Wow.  An opinion piece from over a year ago from a right wing source no less.As I said...given the choice...'
Yes fair enough. But we're talking about the provision to excuse *American citizens* here and the Senator says that Obama specifically requested it. 
[deleted]'
What do you mean so?How is metrics not being accurate but still being used to inform policy not a concern?'
The one who supported telecom immunity the Patriot Act Lieberman over a progressive challenger and FISA Amendments and only hid that behind a rosy change theme?'
Hold it !   You edited/corrected it without acknowledging my post?That is sort of a dick move.You're welcome anyway.
Ha! You're terrible at debating. Just because you may not demonstrate racist behavior doesn't mean Republicans in general don't. It's often said that not every Republican is a racist but every racist is a Republican. The fact that you don't understand th...
You are so right about that. That's why I believe this new confident Obama may get the Democratic Party infected with confidence and get them to stand up and take pride in what the President has accomplished. Hopefully we get a democrat nominee that will...
TYPE IN ALL CAPS!  IT MAKES EVERYTHING BETTER!Sorry but by just about any standard the economy is far far better off than it was when Obama moved into the White House.I realize you have difficulty accepting this fact but it is true whether you want it to...
The eyes see what the mind believes. '
Deficit not debt. '
&gt; Good on foreign policyHad me until there. Gonna have to depart with you on this one massively. His drone war is a neocon's wet dream. 
[deleted]'
I kind of hate the defense that Oh the GOP is worse he should be excused. Why can't we criticize both? (Mexican music plays). '
What Obama requested was to get rid of that entire section of the bill.&gt;The amendment proposed to strike the section Detainee Matters from the bill and replace section 1021 (then titled 1031) with a provision requiring the Administration to clarify th...
&gt;You have two choices.Voting for someone as the lesser of two evils doesn't mean you have to agree with them it just means the other person is worse.
The reason for this thread is the post at the top of the thread.  In the video Republican leaders chose the metrics to prophesize gloom and doom.Those prophecies have been rebutted.Arguing that they should of used different metrics is kinda fun; but it s...
Oh another link to a right wing opinion piece!Don't let me rain on your parade though.  Please continue to pretend that you can't walk out your door without being accosted by a mob of zombies.
Oh I'm well aware that the bulk of donors are supporting both sides.But how much of the Koch Brothers donations are going across the aisle? How many Dems are in Art Pope's pocket?'
Really if he acted the way he has the last couple months he never would have even needed a reelection campaign. Its not like his campaigning likely convinced any republicans to vote for him but I'm sure a lot of people that were on the fence voted for so...
I think you're misunderstanding the point. I never disputed that he was against making detainment mandatory. My video is to show that he never opposed detaining American citizens indefinitely. That is what is disturbing to me. Clarification would still h...
Is it REALLY that stupid though? 9/11 was basically the perfect setup to do pretty much anything. Except instead of martial law they just went with the PATRIOT act and a couple wars (on false evidence I might add). No need need for martial law anyway whe...
This type of sentence really upsets me. It sounds true and in some ways it is true. But in a more fundamental way it is total BS  but it takes time to explain. Pleas see the wiki: http://en.m.wikipedia.org/wiki/History_of_the_United_States_public_debt'
Agree. It's cynically calculated...
[The stock market is not the economy.](http://stocks.about.com/od/marketnews/a/092810The-Stock-Market-Is-Not-The-Economy.htm) [And here.](http://www.forbes.com/sites/jerrybowyer/2013/04/28/the-economy-has-nothing-to-do-with-the-stock-markets-right/)'
&gt; My video is to show that he never opposed detaining American citizens indefinitely.He didn't want the NDAA to have anything in it about detainees at all.  He basically just wanted to keep the status quo.   Your video was misleading; he wanted to dro...
I don't know if you viewed the video but he is not taking credit for the stock market he is saying to paraphrase look the republicans said my policies will cause the stock market to tank - it hasn't  while it's subtle that isn't saying that his policies ...
No I'm not.
[deleted]'
This only works if Biden does street interviews. Beach interviews. Tavern interviews. Sauna interviews...'
I was listening to a Republican talking about the upcoming election next year.  He said America isn't gonna risk trying another *experiment* by voting in a women for President after the last failure.  Even though I knew what he was implying I asked him w...
It wouldn't be r/politics without an Obama cirlce jerk. 
Also code for I bet the false equivalency thing would be thrown out the window immediately if we ever saw the actual difference between how a truly Democrat- controlled Congress would affect America compared to a truly Republican-controlled Congress.'
Wow. Wow. Wow. Another major criticism about Obama just floats away when the layers of BS get peeled away. Not only did he not want to vote for it. He threatened to veto it! Thanks so much for putting the time into responding. I've already sent my conspi...
Once people are unemployed for 2 or so years they don't count in that statistic. Would also like to add a lot if minimum wage jobs are created million of college graduates are taking shit jobs for shit wages and are pushing off paying there loans. Next c...
Ah yes the ever elusive true [Insert Representative of Political Flag I Carry] that lets people think they can dismiss any and all intellectual inconsistency by being more inconsistent.'
I'm saying both are using shoddy statistics.You know they can both be wrong right? It's not a contest over who pointed out their wrongness most recently.
Let's wind the clocks back...a year. http://www.ifyouonlynews.com/humor/watch-what-happens-when-jon-stewart-compares-foxs-coverage-of-ferguson-and-benghazi-an-epic-takedown/
Yeah I agree entirely.  '
I am saying that this thread is about the post with the video at the top of the page.To paraphrase Rumsfeld As you know you discuss the metrics you have not the metrics you might want or wish to have. '
That doesn't mean well these are the only metrics we have so let's form policy based on it is a valid argument.It's okay to not have enough information to make a decision.'
Be that as it may Romney had the opportunity to aim for a lower shit number.'
Go through my history. I'm intensely critical of neoliberalism.
It was during the state of the union. '
&gt; and the inordinate hostility that this president has faced in his years in office.I don't understand why you would say something like this unless you are about 15 years old. Which to be fair a significant portion of /r/politics *is* about 15 years o...
heh'
Ugh it sucks that you were gilded for such a paradoxically generic yet dishonest post.  To your points:&gt; Yeah like those morons who hate Obama for executing a war in violation of the war powers actThe War Powers Act is likely unconstitutional for star...
Not sure what fantasy you live in. Here in reality we know that the disastrous tax cuts in 03 and two unnecessary wars that did the most harm. Before you go off consider anything Pelosi did could have been undone with a Bush veto so look to your own part...
They adjust/correct but to say they tank is a gross overstatement. '
The gross inequality in this country is due to a number of factors and we need to fix it. But way way way more people than just rich bankers depend on a healthy stock market.'
Don't hold your breath... :-\ 
http://www.cnn.com/2009/POLITICS/09/09/joe.wilson/ '
Well of course it says nothing directly about any of those things because its the stock market. But it gives hints about what to expect As the economist Paul Samuelson once put it: xe2x80x9cThe stock market has called nine of the last five recessions.xe2...
&gt; so let me get this straight. wall st is over 18k...which only means that the 'rich' has gotten 'richer'. That's wrong. A healthy stock market benefits way more people than just the rich. Many (most) pension plans depend on a healthy stock market. I'...
If you follow the link in your own video's description you'll find the piece is dated sept 9 09. SOTU happens in January. 
fat cat wallstreet traders get more than they deserve but most of the rest of depend on healthy markets whether we are directly invested or not.'
Ok do not dare say it is total BS because you are just lying to yourself and the world around you.It is true that under Obama's leadership more debt has been added than all other previous presidents combined.Yes of course as a percentage of GDP it is not...
[deleted]'
Why should I disagree with the president's foreign policy? You think we should just leave everything alone and the world will fix itself?
[deleted]'
Obama has done good things and Democrats support better policies than Republicans.  For example we can go down the list you mentioned in your other comment:&gt; endless warSurely you can appreciate the fact that there were 170000 troops in Iraq and Afgha...
Go educate yourself dude'
troll says what? Sorry I won't even bother anything you type because you literally have nothing useful to add to this or any conversation judging by your comment history. 
* 54xc2xb040' wasn't a campaign pledge* He only wanted the 49th parallel to begin with. It was only under pressure from expansionist democrats that he gave support to the more northern boundary and he quickly reverted to his original 49th position on the...
I don't consider a stock market goosed up by cheap money bailouts subsidies etc. to be a healthy one. A healthy stock market wouldn't be so vulnerable that a couple words uttered by the Fed Chairman could send it tumbling off a cliff.
I apologize.  You're absolutely right.  I've heard it called a State of the Union address many times.  Funny how stuff works.
You put a lot of time into this so I'll also try to put some time into a response.First most of you criticism is oddly structured.  I listed bullet-point criticisms and you accused me of oversimplification . . . which is certainly a valid criticism but I...
The stock market is denominated in dollars. A weaker dollar means you need more of them to value the productivity of the enterprises that make up the stock market. The market being up doesn't have to mean the enterprises are more productive. If the dolla...
[deleted]'
This guy breaks down most of your arguments and thats all you respond with?  really though? you seemed pretty intelligent before and able to debate rationally and i was looking forward to reading a good discussion but...ah well.  tis the internet after a...
I don't know what the troll says because I'm not trolling mate. Do you usually resort to calling someone a troll when they disagree with you? (I'm correct by the way Democrats are in NO way shape or form left wing) 
I'll be honest I've had like 12 really good and thoughtful replies and I've only been able to respond to a few.  You can go through my comment history if you want to see more thoughtful responses.This was a good one despite being unnecessarily insulting ...
The debt was 65% of our GDP when Obama took office now it is 103%'
Because everything is about race with you people.'
Lemme get this straight -- the stock market under bush was a sign off the corruption. Under Obama the same market is a sign off success?  That's some fancy mental-gymnastics you've got going on there. 
I'm in the military and every time I hear some jackass saying the phrases he's destroying our country or he's taking away our freedoms I just want to choke them to death. WHAT FUCKIN FREEDOMS DO YOU NO LONGER HAVE? PLEASE FOR THE LOVE OF GOD EXPLAIN EXAC...
I also remember Obama making some promises he didn't keep.
&gt;  but what exactly is wrong with states rights?http://en.wikipedia.org/wiki/Dog-whistle_politics#United_StatesWhat rights aren't afforded to you now that you would have if states rights came to be? None except you would now be able to discriminate as...
Last I checked those PATRIOT ACT laws haven't changed.  We still could have it with any president down the road as long as they keep extending it.  Why Obama wouldn't tear that shit down is beyond me.  He extended it.  So he must think its a good idea to...
Make sure you put money on it.'
On January 21st 2017 your friend will call you and tell you about how Obama rigged the election so Hillary would win and he'll be secretly working with her administration to destroy the country with the nukes Iran will have any day now.
He has got to be the least effective dictator in history. /s'
You have no idea how the world works do you? I can only assume you're young. You don't yet have the required life experience to understand the words that you're typing. Comparing Obama or Romney to Stalin? You need to tone down the hyperbole if you want ...
Were the Republicans in the video right? or wrong?'
God damn it. '
I don't understand dems attacking gun owners.  Does it really get them that many liberal votes? 
[deleted]'
Did you look at where the stock market was in 2008 when Bush left office?That's some fancy mental gymnastics you've got going there!
You know that's really disingenuous and not really correct. besides most of Reddit's users are the children of those people.
I appologize for being a dick this subject just gets me a little angry. I'm not saying Romney would be better at all. What I'm trying to say is that we shouldn't judge obamas actions on what another candidate would have done. If he was to nuke another co...
&gt; He only wanted the 49th parallel to begin with.&gt; 54xc2xb040' wasn't a campaign pledgeThat particular slogan came after.  But his party platform called for all of the Oregon country.  Polk stated in his inauguration that 'Our title to the country ...
Two wrongs don't make a right either.
Where did he get that number?  100 million is literally 33% of the US. The unemployment rate during the great depression was about 25%. You're saying it is worse now?! 
Yeah there are less people working today as a % of the population. However I feel that this is a better measurement of the size of the workforce rather than how easy it is to find a job.'
That's the problem with legislation it's nearly impossible to get rid of it once it passes. Voting against the PATRIOT Act would be political suicide so even if Obama vetoed the bill it would just get overturned. Also security is a multibillion dollar in...
You mean like you don't see our [labor force participation rate](http://data.bls.gov/timeseries/LNS11300000) that is back to 1978 levels? Like you don't care that 7-8 years after the financial collapse the Fed is still pursuing a policy of [zero interest...
Yup that's what happens after a severe recession. No matter who is president. 
I love the double entendre even though you don't know what you're doing. Just because you may not demonstrate racist behavior... *but we know deep down you are* and I love your implication that you and liberals are not nor could possibly be racist. You c...
Y'all need to find Zerohedge.  http://www.zerohedge.com/news/2015-03-21/10-charts-which-show-we-are-much-worse-just-last-economic-crisis
What's the point?
How do I invoke the remind me bot? I want to follow up on this.'
People really do get caught up in the moment and forget about what happened just a few years ago. Which is probably how the GOP keeps getting elected. '
Because the stock market is such a good indicator of the economy...really? '
I like the cut of you jib. You seem passionate you should run. Passionate people who have some awareness of what is going on make good politicians. Bernie Sanders would be one of those (I like Sanders AND I like more Centrist Democrats but I agree the De...
They backtracked twice this week on even soft rumors that rates would go up.  The stock market is doing what it is because 1. companies are not spending and massing cash and 2. the Fed has been dumping $30 billion per month into the banks and bankers poc...
[deleted]'
Should of bet him a million dollars. You'd have a easier time of gettign that.
You are way off base with your assumptions attributing thoughts to me that you've constructed out of whole cloth. That's called a Straw Man. And then all you have is more ad hominems and crude insults not even worth arguing over really. 
He can't close Gitmo. I don't know how many times people have to say that but he can't close it.
Right. Wall Street is doing great. Trillions spent. 6 million thrown off their healthcare plans and forced into the exchanges millions more penalized in 2015 and still 35 million plus uninsured. Lowest labor participation rate in the last 50 years with r...
Obligatory reminder that government reported unemployment figures do not count people who have stopped looking for a job. The actual unemployment figure is quite a bit higher maybe double or more. That being said Romney was certainly referring to the gov...
Okay name me all the whistleblowers that have been prosecuted and explain to me why the Administration shouldn't have gone after them?
I saw what happened. I guess we have different definitions of what tank means. To me tank is what happened the Dow fell to around 8000 a few years ago. About the same time AAPL went from around 200 to 80 (important to me personally). The Dow falling to 1...
[deleted]'
&gt;Let me warn you and let me warn the nation against the smooth evasion which says xe2x80x9cOf course we believe all these things; we believe in social security; we believe in work for the unemployed; we believe in saving homes. Cross our hearts and ho...
Without a teleprompter this is what we can look forward to.....  https://www.youtube.com/watch?v=4XuItt6iuMc'
Can I quote you?'
Yeah but Mr. Obama if we run the tape back then we might find out how much you lied to us about war surveillance and whistleblowing. Thanks for being a hypocrite dickhead.'
How much money does the FED pump into the buddies at Wall Street that gave us this enormous bubble waiting to burst?'
Unemployment rate including people who've stopped looking for a job is 6.6% as per the Bureau of Labor Services (versus 6.2% without them).  The people who've stopped looking (discouraged workers in statistics about this sort of thing) is massively over ...
Notably not all fraternities are conservative.  My fraternity (Phi Mu Alpha Sinfonia) skews *sharply* left nationwide.'
&gt; Voters are stupid and will believe anything the news tells themSo you think fox news can sway the vote of the entire country? If that's true how is Obama in the white house?
What policies did you dislike so much? '
Go for it.'
Or Canada.'
&gt;Their agenda right now is to extract as much wealth out of this country as the people will allow them toWouldn't that make R's dominate the top 50 richest members of congress?[They](http://media.cq.com/50richest2013/) lead 29 to 21 of the top 50. 
They accused him of calling a stand down order in Benghazi intentionally letting those people die for political reasons. Fox News repeated that lie in over 100 segments on their Benghazi conspiracy.'
Grow up being one of the only kids of a different color in a neighborhood and try and come out not a racists for a while. '
The Republicans wouldn't allow them to be tried in civilian courts or be housed in prisons in the US.Outrage over the mere suggestion swept the country in fact. It was around the time of that whole ridiculous Ground Zero Mosque thing.nixonrichard probabl...
Employment is up worked hours are up wages are down and productivity is stagnant at best. Correct me if I'm wrong but isn't all the above together a terrible sign that things are getting worse for americans?
He's been pretty terrible with our civil liberties decent everywhere else though.
/r/actualconspiracies[The Conspiratorial-Industrial Complex: Many organizations throughout the world today attempt to attain power by attracting and exploiting gullible people through the invention of false conspiracies whilst they themselves conspire to...
Anyone that thinks that the DOW has any association with a real economy is dead asleep.'
I'm with you. I voted for him the first time but I wanted Hillary. I then voted Green for Jill Stein the second time around. But good God I spent more time defending Obama then actually defending my own voting choices... Eesh.And it makes me sound like a...
&gt;Good on foreign policy. Pro science.Shitting all over our biggest trade partner(china) Started half a dozen new wars Openly supporting that we spy on the whole world and ramping up cold war tensions to level 10 with Russia is considered Good on forei...
Pretty much every conservative talk show host says that still.If he was trying to become dictator or whatever he already fucking would have.'
The gentleman will sit the gentleman is correct in sitting.'
Fuck off military dick!'
Then explain why the Republicans have been gained more and more seats in Congress over the last three elections?  Their foaming lying and vitriol are gaining them their desired result.'
Can't forget Sharia law.  We were straight up promised Sharia law.
Obama best obama well obviously. But the republics aren't totally done yet Obama still has year to rip up the constitution and throw the country into turmoil. This is right after he admits he is a reptile
So? It's not like that is a new thing. Yeah it's an important point overall but in terms of comparing Obama to past presidents it is irrelevant.  
Yes but he inherited a 1.4T deficit and it has been cut down to ~500B if memory serves.  That huge deficit was why the debt has risen so quickly and cannot fairly be blamed on President Obama.'
I definitely agree that he should pardon Snowden but come on that spy agency has been around long before he took office. To say he built it himself is ridiculous.  '
Dad is that you? '
Tfm has Reagan/bush 84 shirts. Reagan is the frat idol'
[Then how would they explain this trend....](http://www.brookings.edu/~/media/Research/Files/Blogs/2014/01/07-unemployment-stead-drop-barnichon/07-unemployment-steady-drop-figure-1.jpg?la=en)'
Why are guns so important to you that they appear to be your most important political influence appearing to risk overriding all others? Genuine question; I find it hard to relate.'
Simple bull shit answer.  Freedom! Real answer. They are a symbol of self reliance. A gun allows you to deal with some life threatening situations without running and asking the government for help.  Then add to that it is a constitutional right spelled ...
I'd do it.  I think it would be politically savvy rather than suicidal.
Sorry I watch a lot of FOX all day. MSNBC sure is NOT equivalent to FOX news. Nothing matches FOX for over the top rhetoric. I'm still waiting for FOX to discuss the final Benghazi report or apologize for 2 years of bullshit on the topic.
Thanks Obama'
Republicans never admit when they've been wrong. That's worse than compromising.
But but ... Rich getting richer means  trickle-down no?'
Nice try whiner. Next time stay on topic'
Were the Republicans in the posted video working for or against improving things for Americans?'
There's another difference between Clinton or Obama. Can you spot it?
How did he single-handedly do that? (I doubt you'll respond because all you have is talking points)
Cherry-picked without the raw data.  Lots of data in your charts is five years old.Y'all need to find less biased analysis.
Be happy you have a voice here r/conservative'
Give it a few years and they will be talking about how their ideas were the basis for Obamacare and they should get credit for it. They will completely forget how they opposed it so vehemently. '
You mean Supply-Side Jesus? '
Yeah they're dying off. But the funny thing is they're ideologically diverse just like the rest of us. It's interesting how we like to ascribe the same traits to an entire (an rather large!) generation. I think saying conservatives tend to be older is mo...
That wasn't my point. 
I think he is waiting to finish his term in office to become dictator :)'
I'd really love to see more discussion in r/politics of presidents so far removed from today's politics it's absurd just to find out where the two sides fall. It probably wouldn't be consistent without some revisionism. Chester Arthur was bold and decisi...
I consider myself an astronaut. I've just never been to space! 
Trickle Down Economics: http://youtu.be/5EoetIL-MiM'
Except that people rarely wake up as you describe. An incredible irony in today's politics is that the party that invokes original construction reveres the founding fathers and blathers on about American values represents the same political force that al...
You mean clog the tubes?'
All of them are full of shit but even a broken clock is right twice a day.'
Hi `unclefire`. Thank you for participating in /r/Politics. However [your comment](http://www.reddit.com/r/politics/comments/2zsw32/obama_on_gop_critics_run_the_tape_back_president/cpm3f2e) has been removed for the following reason(s):* Your comment does...
Wow. One respected analyst predicted the housing crisis. Meanwhile a fuck ton of average bloggers called the the bubble while most respected analysts trumpeted the big banks' version until the bitter end.'
Devalued?  Really?  The last I checked the dollar was pretty darn strong against the other currencies.   And inflation has been below the 2% fed target for some time now.The ***US** stock markets are in dollars.  There are markets in other countries you ...
No not wrong. Democrats claiming their policies created a huge rise in the stock market making the wealthy wealthier ... not wrong.  The Democrats hating the wealthy becoming wealthier ... not wrong. Democrats using divisive rhetoric about the wealthy in...
OMG a 200 point drop from the all time high of the dow is the end of the world!!!  /s   &lt;--- is it even necessary?'
That's the point of monetary policy--  you lower interest rates to stimulate the economy.   Once you get to zero there ain't a who lot more you can do beyond resorting to QE.  This last recession wasn't like the others where it was relatively easy to goo...
http://www.forbes.com/sites/jeffreydorfman/2014/09/04/if-president-obama-told-the-truth-about-the-economy/This article will offer you fresh perspective. I doubt you will be able to finish it because it will cause every little brainwashed cell in your hea...
It's the best marker if you want something that the average Joe will be impressed by. Oooh the number got bigger!'
They're forward looking indicators.  Stock prices are based (partially) on future expectations of earnings.
[deleted]'
The rich have gotten richer?   Really?   So the millions of people who have 401k's IRAs and other investments are all rich?   Pension funds which invest in the markets are all for rich people?   
He did pretty much all that he can do.   He's isn't going to do much of anything in the remaining two years.   
Wow you're just all over the place and wrong again because nothing you just wrote is not even what I was talking about in my last comment. But let's just take this one item as illustrative of the whole: Democrats claiming their policies created a huge ri...
Yup--  if one side is all doom and gloom how is it a problem to say they were wrong?  Perception is reality.  In spite of the numbers/facts most conservatives are perpetually bloviating about how he's destroying the country.   In their mind we're on the ...
um read your link. It decreased.'
Oh so none of the things cited are actually true?The down didn't bottom out in 2009 and now at all time highs?Romney said he'd lower unemployment to &lt; 6% but Obama would be bad (and yet we're lower than 6% well before Romney's prediction)Job growth ha...
Uh I suppose you haven't been paying attention for like the last 50 years.   The fed lowers rates to stimulate the economy.  It raises rates to slow it down b/c of inflation.   Your alternative would be what?
Yeah you're summing it up pretty well. I can't believe there are people who want the numbers to be bad  so the president looks bad; quite unpatriotic really. However our nation never addressed the growing bubbles in our economy so I'm always hesitant to ...
I'm not talking about the headline I'm more so talking about the comments within this thread. Like the one that basically said anyone who criticizes Obama is irrational. 
Let rates adjust based on the market. There are times that are good to borrow and other times not. Interest rates signal that but distorting that signal is what causes economic problems.Artificially lowering the rates when it's a bad idea to borrow-but l...
Except the easy money policy  is what set up the economic collapse of 2008.'
Fair enough.  I agree with you on that one.  There's plenty to criticize but that goes for pretty much any President.
I appreciate the response just looking for some insight. Thanks.'
There was way more to it than just easy money (meaning low interest rates).    There was easy money from a poor lending practice perspective as well  (ARMS liar loans pick-a-pay interest only 100% LTV CDSs  MBS fraud etc. etc.)   '
..damn you just got my wife all excited.'
Wrong. '
Lol Obama isn't center left. He's center right if anything.
You're right. The 1% owns like 30-40% of traded stocks. Presidents have very little effect on the stock market and a good stock market really just means like 15% of the population is doing well. It doesn't change the fact 30% of the population makes mini...
Well he said he could. And he hasn't. That's the point. Realistically I understand it can't and won't be closed. Which makes me annoyed with the populace-appeasing rhetoric of Obama. Easy to say what people want to hear. A lot harder to put those words i...
This is not a statement that someone who actually understands economics or the economy would make. Maybe stick to guitars and veganism.'
I had to delete some of my response to keep this within 10000 characters.  Please don't take that to mean that I'm conceding the point on any of your claims that went unanswered.&gt; What's more the instability resulting from the fall of Gaddafi has led ...
Reagan is the idol for people who go on TFM which only represents a portion of the much larger Greek system (and a part that I personally really hate as it gives a really negative impression of what a brotherhood ought to be).'
This is on topic. This headline only makes sense if you are deeply steeped into a reality molded by cable television and mass media. There are levels to this headline. If you don't see at least a few of them you need to take a step back and look at what ...
The majority of fraternitites at large state schools have that type of culture. Brotherhood varies by frat and school of course but in large state schools in places like the big ten and sec tfm is the style. '
&gt; But again this is somewhat beside the point since your complaint was over Obama breaking the War Powers Act. Even if true it's a hollow criticism given the dubious legal standing of the War Powers Act to begin with and the net result of A) Saving Be...
That is then the attitude of large state schools and not an innate thing in fraternities.'
It's around 50% of the working class.The thing is we do have funds invested but it's not enough to make an impact the way you think. Here is an article that breaks down the problem: http://www.bloomberg.com/news/articles/2014-03-12/stock-market-surge-byp...
When I say related I really mean dependent on each other.I've been told my professor like this:You don't have to have an I.R.A. and gamble with your retirement. You could keep it and invest in yourself or another business. Spend it and keep the economy b...
I'm all over the place? In my comment a day ago I made this statement Do you actually think you had something to do with the stock market rise? Here all along I thought it was the Federal Reserve and evil corporation policies.And it was the Federal Reser...
Good on foreign policy is laughable.'
What aspects of the economy are at record lows? '
This should help-The unemployment record is really above 15%.Economy is on declineLabor participation fallsSource: http://www.zerohedge.com/news/2015-03-21/10-charts-which-show-we-are-much-worse-just-last-economic-crisisCorporate Greed Hoards Cash &amp; ...
The gdi's (non frat) and foreign students aren't like that they're rather liberal actually. The more bro types are just highly attracted to fraternities in state schools. Even private school fraternities are bro relative to the rest of the school otherwi...
There's no guarantee it would have improved nearly as much with the sorts of policies Romney was pushing.  And the debt would almost certainly be larger.
Well stated and agreed. The insane wealth gain by the richest is unsustainable.'
Ah but I've been to (and graduated from) law schoolxe2x80x94a top one at that. ;)
You seem to have a serious misunderstanding of what GDP is how it is calculated (and the reasons why it is calculated as it is) and what it is supposed to measure. You may want to begin here: https://www.khanacademy.org/economics-finance-domain/macroecon...
What other metrics would you suggest for measuring real prosperity? GDP has been a pretty good proxy (albeit not perfect) for measuring prosperity both across time and across other countries.'
Spending a million dollars on bombs does not have the same economic effect as does on schools or a new form of technology. Looking at just plain spending doesn't tell us much about how well people are doing.We should look at people's purchasing power and...
&gt;You seem to have a serious misunderstanding of what GDP is how it is calculated (and the reasons why it is calculated as it is) and what it is supposed to measure. Look if you're ignoring business to business transactions you're missing a huge portio...
Hi `Pocahontas_Spaceman`. Thank you for participating in /r/Politics. However [your comment](https://www.reddit.com/r/politics/comments/2zsw32/obama_on_gop_critics_run_the_tape_back_president/cpm6l1y) has been removed for the following reason(s):* Your c...
Thanks for the example.I'm not a Republican or a Democrat. I just noticed how this place is a partisan shit hole of a subreddit.Take care!
Erm  what?'
lol. But the brand new account trolling me is fine. Bang up job guys.'
There's no need to insinuate I'm young or naive. My assertion that Obama faces unprecedented opposition from Republicans in no way *means that I'm insinuating* that other presidents had it easy or were given a pass.However *even if partisan politics is n...
Uhhh... that's an interesting worldview I guess? I'd suggest you seek some diversity in your news outlets / sources. I'm detecting some extremely concerning biases here.
For what it's worth I revised my statement to exclude pre-20th century presidents. I really don't think my statement should have been stretched to include presidents who were elected under an archaic political system that has little resemblance to our mo...
I agree he dropped the ball with the ACA but I believe you're underestimating him as naive. As I recall had the Democratic leadership and its representatives in Congress had a firmer backbone and stood up to the Republican's spin Obama would have had a s...
What have republicans done to fix things?'
What's that got to do with what I said you nutcase?
You posted doom and gloom and implying that things were not as good as the original post suggested.  This also raised the implication that it is our President's fault - since that was the gist of the original post.The Republican Party is blocking any pro...
[deleted]'
[deleted]'
I know you're a confirmed lefty. ;)
Unfortunately I've no political bias so no I don't. I think the problems that the u.s faces are deeply routed in their practices I.e money in politics more than the stupidity of one particular party.And as I said correct me if I'm wrong from what I can s...
There is no argument designed in my head.An unbiased clear and critical examination of the current United States political landscape will show that one party is toxic to the U.S. economy and the majority of its citizens.One party is advocating theocracy ...
Yes right exactly but what does that have to do with my point? Oh yeah right the argument you had in your head with your impression of my assumed position. Again my point was nothing to do with any inter party politics only on the it's a sunny outlook im...
It's hilariously ironic that this post was the most upvoted response hahaha like how the ignorant liberal base is no different than the conservative one. Well at least they are stupid in favor of helping people I guess.
It isn't an attitude it is fact if you take emotion out of it. If I'm talking to neurologist about their research they are damn well going to have to simplify concepts. A lot of the general public don't know a lot about the economy international politics...";

$api = new TextRankFacade();
// English implementation for stopwords/junk words:
$stopWords = new English();
$api->setStopWords($stopWords);

// Array of the most important keywords:
$result = $api->getOnlyKeyWords($text); 

// Array of the sentences from the most important part of the text:
$result = $api->getHighlights($text); 

// Array of the most important sentences from the text:
$result = $api->summarizeTextBasic($text);

echo var_dump($result);
?>

