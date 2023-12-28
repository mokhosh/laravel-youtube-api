<?php

namespace Mokhosh\LaravelYoutubeApi;

use Google\Service\YouTube\Caption;
use Google\Service\YouTube\CaptionListResponse;
use Mokhosh\LaravelYoutubeApi\Auth\AuthService;

class CaptionService extends AuthService
{
    public function captionsListById(array|string $part, string $videoId, array $params): CaptionListResponse
    {
        $params = array_filter($params);

        $service = new \Google_Service_YouTube($this->client);

        return $service->captions->listCaptions($part, $videoId, $params);
    }

    public function uploadCaption(array|string $googleToken, string $videoId, string $captionPath, string $language, string $name): Caption|bool
    {
        $setAccessToken = $this->setAccessToken($googleToken);
        if (!$setAccessToken) {
            return false;
        }

        $youtube = new \Google_Service_YouTube($this->client);
        $snippet = new \Google_Service_YouTube_CaptionSnippet();

        $snippet->setVideoId($videoId);
        $snippet->setLanguage($language);
        $snippet->setName($name);

        $caption = new \Google_Service_YouTube_Caption();
        $caption->setSnippet($snippet);

        return $youtube->captions->insert(
            "status,snippet",
            $caption,
            [
                'data' => file_get_contents($captionPath),
                'mimeType' => '*/*',
                'uploadType' => 'multipart'
            ]
        );
    }

    public function deleteCaption(array|string $googleToken, string $id, array $params = [])
    {
        $setAccessToken = $this->setAccessToken($googleToken);
        if (!$setAccessToken) {
            return false;
        }

        $params = array_filter($params);

        $service = new \Google_Service_YouTube($this->client);

        return $service->captions->delete($id, $params);
    }
}
