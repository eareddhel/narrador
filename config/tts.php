<?php

use App\Core\Env;

return [
    'binary' => Env::getString('TTS_BINARY'),
    'default_voice' => 'es-CL-LorenzoNeural',
    'audio_path' => Env::getString('AUDIO_PATH'),
    'temp_path' => Env::getString('TEMP_PATH'),
];
