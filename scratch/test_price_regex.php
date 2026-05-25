<?php
$tests = [
    "TikTok Likes [Max: 10K] - \$0.30" => [0.3, 0.42],
    "Instagram Followers | 0.3\$" => [0.3, 0.42],
    "tiktok j aime 0.3" => [0.3, 0.42],
    "Spotify Plays 1.5M - 0.45" => [0.45, 0.63],
    "YouTube Views 10K (Speed 0.3K/day) - 0.30" => [0.3, 0.42],
    "Free Views 0.0" => [0, 0]
];

foreach ($tests as $title => $rates) {
    $rate = $rates[0];
    $calculatedRate = $rates[1];
    $name = $title;
    
    if ($rate > 0) {
        $possibleFormats = [
            number_format($rate, 4, '.', ''),
            number_format($rate, 3, '.', ''),
            number_format($rate, 2, '.', ''),
            (string)(float)$rate
        ];
        $possibleFormats = array_unique($possibleFormats);
        
        // Trier par longueur décroissante pour éviter que '0.3' remplace partiellement '0.30'
        usort($possibleFormats, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        // Formatage du prix calculé
        $calcStr = (float)$calculatedRate == floor($calculatedRate) ? (string)(int)$calculatedRate : rtrim(rtrim(number_format($calculatedRate, 4, '.', ''), '0'), '.');
        
        foreach ($possibleFormats as $rf) {
            // Remplacement de $X.XX ou X.XX$ sans utiliser les backreferences directes problématiques ($0, $1 etc)
            $name = preg_replace_callback('/\$' . preg_quote($rf, '/') . '(?!\d)/', function() use ($calcStr) { return '$' . $calcStr; }, $name);
            $name = preg_replace_callback('/(?<!\d)' . preg_quote($rf, '/') . '\$/', function() use ($calcStr) { return $calcStr . '$'; }, $name);
            
            // Remplacement à la toute fin de la chaîne, s'il est précédé d'un espace, d'un tiret ou d'un pipe
            $name = preg_replace_callback('/([\s\-\|]+)' . preg_quote($rf, '/') . '(?!\d)$/', function($matches) use ($calcStr) { return $matches[1] . $calcStr; }, $name);
        }
    }
    
    echo "Original: $title\nModifié : $name\n\n";
}
