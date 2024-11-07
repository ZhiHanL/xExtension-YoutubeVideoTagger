<?php

class YouTubeVideoTaggerExtension extends Minz_Extension
{
    const YT_VIDEO_SUBSTR = "yt:video:";
    const YT_DATA_API_URL = "https://youtube.googleapis.com/youtube/v3/videos";

    public function init()
    {
        $this->registerHook("entry_before_insert", [$this, "tagVideo"]);
    }

    public function handleConfigureAction()
    {
        $this->registerTranslates();

        if (Minz_Request::isPost()) {
            $config = [
                "youtube_api_token" => Minz_Request::paramString(
                    "youtube_api_token",
                    ""
                ),
                "short_duration" => Minz_Request::paramString(
                    "short_duration",
                    0
                ),
            ];

            FreshRSS_Context::$user_conf->YouTubeVideoTagger = $config;
            FreshRSS_Context::$user_conf->save();
        }
    }

    public function getShortDuration(): int
    {
        return intval(
            FreshRSS_Context::$user_conf->YouTubeVideoTagger["short_duration"]
        );
    }

    public function getYoutubeAPIToken(): string
    {
        return FreshRSS_Context::$user_conf->YouTubeVideoTagger["youtube_api_token"];
    }

    public function tagVideo($entry)
    {
        try {
            // Check if entry is a youtube video
            if (
                !(
                    is_object($entry) &&
                    strpos($entry->guid(), self::YT_VIDEO_SUBSTR) !== false
                )
            ) {
                Minz_Log::debug(
                    "YouTubeVideoTagger: Entry is not a youtube video"
                );
                return $entry;
            }

            // Get youtube video id
            $ytID = substr($entry->guid(), strlen(self::YT_VIDEO_SUBSTR));

            // Get video details
            $videoDetails = self::getVideoDetails($entry, $ytID);

            // Tag shorts
            //
            // Append [shorts] to title if the length < configured seconds and  length > 0 (scheduled streams)
            $duration = new DateInterval(
                $videoDetails->contentDetails->duration
            );
            $durationSeconds = self::intervalToSeconds($duration);
            if (
                $durationSeconds < $this->getShortDuration() &&
                $durationSeconds > 0
            ) {
                Minz_Log::debug(
                    "YouTubeVideoTagger-EntryBeforeInsert - Short Detected, tagging short"
                );
                $entry->_title("[Shorts] " . $entry->title());
            }

            // Tag livestreams
            //
            // Append [Upcoming] to title if upcoming livestream
            // Append [Live] to title if live livestream
            $livestreamData = $videoDetails->snippet->liveBroadcastContent;
            switch ($livestreamData) {
                case "upcoming":
                    Minz_Log::debug(
                        "YouTubeVideoTagger: Upcoming livestream deteceted, tagging upcoming"
                    );
                    $entry->_title("[Upcoming] " . $entry->title());
                    break;
                case "live":
                    Minz_Log::debug(
                        "YouTubeVideoTagger: Upcoming livestream deteceted, tagging live"
                    );
                    $entry->_title("[Live] " . $entry->title());
                    break;
                default:
                    Minz_Log::debug(
                        "YouTubeVideoTagger: Livestream not detected, skip tagging"
                    );
            }

            // Log finalized title name
            Minz_Log::debug(
                "YouTubeVideoTagger: Finalized Entry Name: " .
                    $entry->title()
            );
        } catch (Exception $e) {
            Minz_Log::error(
                "YouTubeVideoTagger: " . $e->getMessage()
            );
        }

        Minz_Log::debug("YouTubeVideoTagger - END");
        return $entry;
    }

    private function intervalToSeconds(DateInterval $interval): int
    {
        return $interval->days * 86400 +
            $interval->h * 3600 +
            $interval->i * 60 +
            $interval->s;
    }

    private function getVideoDetails($entry, $ytID): mixed
    {
        $result = false;
        try {
            if (is_object($entry)) {
                $origAllowUrlFopen = ini_get("allow_url_fopen");

                $query_part = "part=contentDetails%2Csnippet";
                $query_id = "id=" . $ytID;
                $query_key = "key=" . $this->getYoutubeAPIToken();

                try {
                    $full_url =
                        self::YT_DATA_API_URL .
                        "?" .
                        $query_part .
                        "&" .
                        $query_id .
                        "&" .
                        $query_key;

                    Minz_Log::debug(
                        "YouTubeVideoTagger: getVideoDetails full url: " .
                            $full_url
                    );

                    ini_set("allow_url_fopen", 1);
                    $json = file_get_contents($full_url);
                    Minz_Log::debug(
                        "YouTubeVideoTagger: getVideoDetails response: " .
                            serialize($json)
                    );
                } finally {
                    ini_set("allow_url_fopen", $origAllowUrlFopen);
                }

                $data = json_decode($json);
            }
        } catch (Exception $e) {
            $result = false;
            Minz_Log::error(
                "YouTubeVideoTagger: getVideoDetails ERROR " . $e->getMessage()
            );
        }

        return $data->items[0];
    }
}
