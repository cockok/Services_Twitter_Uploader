<?php
/**
 * An abstract interface for OAuthUploader Services
 *
 * PHP version 5.2.0+
 *
 * Copyright 2010 withgod
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category Services
 * @package  Services_Twitter_Uploader
 * @author   cockok <cockok@cheki.net>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version  GIT: $Id$
 * @link     https://github.com/withgod/Services_Twitter_Uploader
 */

require_once 'HTTP/Request2.php';
require_once 'Services/Twitter/Uploader.php';

/**
 * implementation OAuthUploader Services
 *
 * @category Services
 * @package  Services_Twitter_Uploader
 * @author   cockok <cockok@cheki.net>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version  Release: 0.1.0
 * @link     https://github.com/withgod/Services_Twitter_Uploader
 * @link     http://twitvideo.jp/terms/api
 * @see      HTTP_Request2
 */
class Services_Twitter_Uploader_TwitvideoUploader extends Services_Twitter_Uploader
{
    /**
     * post keyword
     * @var string
     */
    protected $postKeyword = null;

    /**
     * upload endpoint
     * @var string
     */
    protected $uploadUrl = "http://api.twitvideo.jp/2/upload.json";

    /**
     * Constructor
     *
     * @param HTTP_OAuth_Consumer $oauth   oauth consumer
     * @param string              $apiKey  required
     * @param HTTP_Request2       $request http provider
     *
     * @see HTTP_OAuth_Consumer
     * @see HTTP_Request2
     * @throws Services_Twitter_Uploader_Exception
     */
    public function __construct(
        HTTP_OAuth_Consumer $oauth = null, $apiKey = null,
        HTTP_Request2 $request = null
    ) {
        parent::__construct($oauth, $apiKey, $request);
    }

    /**
     * preUpload implementation
     *
     * @return void
     */
    protected function preUpload()
    {
        $this->lastRequest->setConfig('ssl_verify_peer', false);
        if (!empty($this->apiKey)) {
          $this->lastRequest->addPostParameter('consumerkey', $this->apiKey);
        }
        if (!empty($this->postMessage)) {
            $this->lastRequest->addPostParameter('message', $this->postMessage);
        }
        if (!empty($this->postKeyword)) {
            $this->lastRequest->addPostParameter('keyword', $this->postKeyword);
        }
        try {
            $this->lastRequest->addUpload('media', $this->postFile);
        } catch (HTTP_Request2_Exception $e) {
            throw new Services_Twitter_Uploader_Exception(
                'cannot open file ' . $this->postFile
            );
        }
        $this->lastRequest->setHeader(
            array(
                'X-Auth-Service-Provider'            => self::TWITTER_VERIFY_CREDENTIALS_JSON,
                'X-Verify-Credentials-Authorization' => $this->genVerifyHeader(
                    self::TWITTER_VERIFY_CREDENTIALS_JSON
                )
            )
        );
    }

    /**
     * postUpload implementation
     *
     * @return string|null image url
     */
    protected function postUpload()
    {
        $body = $this->postUploadCheck($this->response, 200);
        $resp = json_decode($body);

        if (is_object($resp) && property_exists($resp, 'media') && !empty($resp->media)) {
            return $resp->media->url;
        }
        throw new Services_Twitter_Uploader_Exception(
            'unKnown response [' . $body . ']'
        );
    }
}
