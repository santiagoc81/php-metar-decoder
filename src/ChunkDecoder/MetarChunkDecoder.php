<?php

namespace MetarDecoder\ChunkDecoder;

abstract class MetarChunkDecoder
{
    /**
     * Extract the corresponding chunk from the remaining metar
     * @return matches array if any match (null if no match), + updated remaining metar
     */
    public function consume($remaining_metar)
    {
        $chunk_regexp = $this->getRegexp();
        
        // try to match chunk's regexp on remaining metar
        if (preg_match($chunk_regexp, $remaining_metar, $matches)) {
            $found = $matches;
        }else{
            $found = null;
        }
        
        // consume what has been previously found with the same regexp
        $new_remaining_metar = preg_replace($chunk_regexp, '', $remaining_metar, 1);
        
        return array(
            'found' => $found,
            'remaining' => $new_remaining_metar
        );
    }
}
