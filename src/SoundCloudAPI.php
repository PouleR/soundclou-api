<?php

namespace PouleR\SoundCloudAPI;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

/**
 * Class SoundCloudAPI
 */
class SoundCloudAPI
{
    private const MAX_UPLOAD_SIZE = (500 * 1024 * 1024);

    /**
     * @param SoundCloudClient $client
     */
    public function __construct(readonly private SoundCloudClient $client)
    {
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->client->setAccessToken($accessToken);
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->client->setClientId($clientId);
    }

    /**
     * Get a user
     * https://developers.soundcloud.com/docs/api/reference#users
     *
     * @param int|null $userId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function getUser(?int $userId = null): object|array
    {
        $url = 'me';

        if (null !== $userId) {
            $url = sprintf('users/%d', $userId);
        }

        return $this->client->apiRequest('GET', $url);
    }

    /**
     * Get a track
     * https://developers.soundcloud.com/docs/api/reference#tracks
     *
     * @param int         $trackId
     * @param string|null $secretToken
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function getTrack(int $trackId, ?string $secretToken = null): object|array
    {
        $url = sprintf('tracks/%d', $trackId);

        if (null !== $secretToken) {
            $url = sprintf('%s?secret_token=%s', $url, $secretToken);
        }

        return $this->client->apiRequest('GET', $url);
    }

    /**
     * List of tracks of the user
     * https://developers.soundcloud.com/docs/api/reference#users
     *
     * @param int|null $userId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function getTracks(?int $userId = null): object|array
    {
        $url = 'me/tracks';

        if (null !== $userId) {
            $url = sprintf('users/%d/tracks', $userId);
        }

        return $this->client->apiRequest('GET', $url);
    }

    /**
     * @param int $trackId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function getStreamUrlsForTrack(int $trackId): object|array
    {
        $url = sprintf('tracks/%d/streams', $trackId);

        return $this->client->apiRequest('GET', $url);
    }

    /**
     * @param int $trackId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function repostTrack(int $trackId): object|array
    {
        $url = sprintf('e1/me/track_reposts/%d', $trackId);

        return $this->client->apiRequest('PUT', $url);
    }

    /**
     * @param int $trackId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function likeTrack(int $trackId): object|array
    {
        $url = sprintf('e1/me/track_likes/%d', $trackId);

        return $this->client->apiRequest('PUT', $url);
    }

    /**
     * @param int    $trackId
     * @param string $comment
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function commentOnTrack(int $trackId, string $comment): object|array
    {
        $url = sprintf('tracks/%d/comments', $trackId);
        $data = [
            'comment[body]' => $comment,
            'comment[timestamp]' => 0
        ];

        return $this->client->apiRequest('POST', $url, [], $data);
    }

    /**
     * Follow a user
     * https://developers.soundcloud.com/docs/api/reference#me
     *
     * @param int $userId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function followUser(int $userId): object|array
    {
        $url = sprintf('me/followings/%d', $userId);

        return $this->client->apiRequest('PUT', $url);
    }

    /**
     * Unfollow a user
     * https://developers.soundcloud.com/docs/api/reference#me
     *
     * @param int $userId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function unFollowUser(int $userId): object|array
    {
        $url = sprintf('me/followings/%d', $userId);

        return $this->client->apiRequest('DELETE', $url);
    }

    /**
     * List of users who are followed by the user
     * https://developers.soundcloud.com/docs/api/reference#me
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function getFollowings(): object|array
    {
        return $this->client->apiRequest('GET', 'me/followings');
    }

    /**
     * The resolve resource allows you to lookup and access API resources when you only know the SoundCloud.com URL.
     * https://developers.soundcloud.com/docs/api/reference#resolve
     *
     * @param string $url
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function resolveUrl(string $url): object|array
    {
        $url = sprintf('resolve?url=%s', $url);

        return $this->client->apiRequest('GET', $url);
    }

    /**
     * Search for tracks
     *
     * @param string $query
     *
     * @return object|array
     *
     * @throws SoundCloudAPIException
     */
    public function searchTracks(string $query): object|array
    {
        $searchUrl = sprintf('tracks?q=%s', urlencode($query));

        return $this->client->apiRequest('GET', $searchUrl);
    }

    /**
     * Search for playlists
     *
     * @param string $query
     *
     * @return object|array
     *
     * @throws SoundCloudAPIException
     */
    public function searchPlaylists(string $query): object|array
    {
        $searchUrl = sprintf('playlists?q=%s', urlencode($query));

        return $this->client->apiRequest('GET', $searchUrl);
    }

    /**
     * Search for users
     *
     * @param string $query
     *
     * @return object|array
     *
     * @throws SoundCloudAPIException
     */
    public function searchUsers(string $query): object|array
    {
        $searchUrl = sprintf('users?q=%s', urlencode($query));

        return $this->client->apiRequest('GET', $searchUrl);
    }

    /**
     * @param int $trackId
     *
     * @return string|null
     *
     * @throws SoundCloudAPIException
     */
    public function getStreamUrl(int $trackId): ?string
    {
        if (empty($this->client->getAccessToken())) {
            return null;
        }

        $url = sprintf('tracks/%d/stream', $trackId);

        return $this->client->urlRequest('GET', $url);
    }

    /**
     * @param string $clientSecret
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function authenticate(string $clientSecret): object|array
    {
        $bodyData = sprintf(
            'client_id=%s&client_secret=%s&grant_type=client_credentials',
            $this->client->getClientId(),
            $clientSecret
        );

        return $this->client->apiRequest(
            'POST',
            'oauth2/token',
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            $bodyData
        );
    }

    /**
     * @param string $clientSecret
     * @param string $refreshToken
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function refreshToken(string $clientSecret, string $refreshToken): object|array
    {
        $bodyData = sprintf(
            'client_id=%s&client_secret=%s&grant_type=refresh_token&refresh_token=%s',
            $this->client->getClientId(),
            $clientSecret,
            $refreshToken
        );

        return $this->client->apiRequest(
            'POST',
            'oauth2/token',
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            $bodyData
        );
    }

    /**
     * @param string $title
     * @param string $trackFilePath
     * @param string $description
     * @param string $artworkFilePath
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function uploadTrack(string $title, string $trackFilePath, string $description = '', string $artworkFilePath = ''): object|array
    {
        if (!is_file($trackFilePath)) {
            throw new SoundCloudAPIException(sprintf('The file \'%s\' could not be found', $trackFilePath));
        }

        $size = filesize($trackFilePath);

        if ($size > self::MAX_UPLOAD_SIZE) {
            throw new SoundCloudAPIException(sprintf('The file \'%s\' should not exceed %d bytes, current size is %d bytes', $trackFilePath, self::MAX_UPLOAD_SIZE, $size));
        }

        $formFields = [
            'track[title]' => $title,
            'track[asset_data]' => DataPart::fromPath(realpath($trackFilePath)),
            'track[description]' => $description
        ];

        if (!empty($artworkFilePath)) {
            $formFields['track[artwork_data]'] = DataPart::fromPath(realpath($artworkFilePath));
        }

        $formData = new FormDataPart($formFields);

        return $this->client->apiRequest(
            'POST',
            'tracks',
            $formData->getPreparedHeaders()->toArray(),
            $formData->bodyToIterable()
        );
    }

    /**
     * @param int $trackId
     *
     * @return array|object
     *
     * @throws SoundCloudAPIException
     */
    public function deleteTrack(int $trackId): object|array
    {
        $url = sprintf('tracks/%d', $trackId);

        return $this->client->apiRequest('DELETE', $url);
    }
}
