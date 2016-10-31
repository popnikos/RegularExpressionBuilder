# RegularExpressionBuilder
## Installation
You can use composer to add github repository
<pre><code class="json">
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/popnikos/RegularExpressionBuilder.git"
        }
    ],
    "require": {
        "popnikos/RegularExpressionBuilder": "*"
    }
}
</code></pre>

## Examples
<pre><code class="php">

use Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder;

$builder = new PatternBuilder();
$builder
        ->startWith('start string')
                ->startCapture()
                    ->contains('capture')
                    ->orExp('other capture')
                ->endCapture()
        ->endsWith('end of string');

echo "{$builder}"; // "/^start string(capture|other capture)end of string$/"
</code></pre>

Adding a subpattern

```php
$pattern = new PatternBuilder();

$pattern
        ->contains('toto')
        ->multiline() // PCRE_MULTILINE Modifier
        ->ungreedy() // PCRE_UNGREEDY Modifier
        ->subPattern()
            ->contains('test')
            ->orExp('tset')
            ->caseless() // PCRE_CASELESS modifier only applies on subpattern
            ->end(); // End of supattern 
        ->contains('something');
    
echo "{$pattern}"; // "/toto(?i:test|tset)something/mU"
```
