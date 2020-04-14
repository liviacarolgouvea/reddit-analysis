<?php
 
class WordCloud
{
    var $words = array();
 
    function __construct($text)
    {
        $text = preg_replace('/\W/', ' ', $text);
 
        $words = explode(' ', $text);        
        foreach ($words as $key => $value)
        {
                $this->addWord($value);
        }
 
    }
 
    function addWord($word, $value = 1)
    {
        $word = strtolower($word);
 
        if (array_key_exists($word, $this->words))
            $this->words[$word] += $value;
        else
            $this->words[$word] = $value;
    }
 
 
    function getSize($percent)
    {
        $size = "font-size: ";
 
        if ($percent >= 99)
            $size .= "4em;";
        else if ($percent >= 95)
            $size .= "3.8em;";
        else if ($percent >= 80)
            $size .= "3.5em;";
        else if ($percent >= 70)
            $size .= "3em;";
        else if ($percent >= 60)
            $size .= "2.8em;";
        else if ($percent >= 50)
            $size .= "2.5em;";
        else if ($percent >= 40)
            $size .= "2.3em;";
        else if ($percent >= 30)
            $size .= "2.1em;";
        else if ($percent >= 25)
            $size .= "2.0em;";
        else if ($percent >= 20)
            $size .= "1.8em;";
        else if ($percent >= 15)
            $size .= "1.6em;";
        else if ($percent >= 10)
            $size .= "1.3em;";
        else if ($percent >= 5)
            $size .= "1.0em;";
        else
            $size .= "0.8em;";
 
        return $size;
    }
 
    function showCloud($show_freq = false)
    {
        $this->max = max($this->words);
 
        foreach ($this->words as $word => $freq)
        {
            if(!empty($word))
            {
                $size = $this->getSize(($freq / $this->max) * 100);
                if($show_freq) $disp_freq = "($freq)"; else $disp_freq = "";
 
                $out .= "<span style='font-family: Tahoma; padding: 4px 4px 4px 4px; letter-spacing: 3px; $size'>
                            &nbsp; {$word}<sup>$disp_freq</sup> &nbsp; </span>";
            }
        }
 
        return $out;
    }
 
}
?>

<?php
 
$txt = "The GOP convinced millions of people that Obama was an agent of some foreign state hellbent on destroying the nation. They claimed that he was setting up death panels. They claimed that he wasn't American. They claimed he hated white people. They claimed he hated black people. They claimed he was a communist, a socialist, an atheist and a muslim. They questioned his loyalty to America. They've disrespected him during the STOTU a joint session of congress by calling him a liar. They made him the first President to ever to be denied his choice of date in delivering an address to a joint session of congress. They claimed he would increase the deficit. They've tried to sandbag peace negotiations in the middle east. They tried to sandbag all of his efforts to pass meaningful jobs bills that would greatly help America's declining infrastructure. They've cited bible verses as rationale for murdering President Obama, leaving his wife a widow and his children fatherless. They've actively encouraged foreign leaders of other nations to openly disrespect him on American soil. They've insulted his wife and his two young daughters on multiple occasions.

When his term is over President Obama needs to tour the country with a banner on his bus saying No quarter given, no quarter taken. Then he needs to play the GOPs greatest hits for two hours with mocking commentary at every stadium in the US.";
$cloud = new WordCloud($txt);
echo $cloud->showCloud(ture);
 
?>