<?php


namespace AppBundle\Helper\Ziggeo;

/**
 * Class ZiggeoConnect
 * @package AppBundle\Helper\Ziggeo
 */
class ZiggeoConnect
{
    private $application;

    function __construct($application) {
        $this->application = $application;
    }

    private function curl($url) {
        $server_api_url = $this->application->config()->get("server_api_url");
        $regions = $this->application->config()->get("regions");
        foreach ($regions as $key => $value)
            if (strpos($this->application->token(), $key) === 0)
                $server_api_url = $value;

        $curl = curl_init($server_api_url . "/v1" . $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);

        if( (ini_get('safe_mode') === 'Off' || ini_get('safe_mode') === false) && ini_get('open_basedir') === '' ) {
            //we can only make curl follow the redirects if the safe mod is off and open_basedir is not set, otherwise it would throw error
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $this->application->token() . ":" . $this->application->private_key());

        return $curl;
    }

    private function assert_state($assert_states, $state, $result = array()) {
        if (!is_array($assert_states))
            $assert_states = array($assert_states);
        foreach ($assert_states as $assert_state)
            if ($assert_state == $state)
                return;
        throw new ZiggeoException($state, $result);
    }

    function get($url, $data = array(), $assert_state = ZiggeoException::HTTP_STATUS_OK) {
        if (count($data) > 0)
            $url .= '?' . http_build_query($data);
        $curl = $this->curl($url);
        $result = curl_exec($curl);
        $this->assert_state($assert_state, curl_getinfo($curl, CURLINFO_HTTP_CODE), $result);
        return $result;
    }

    function getJSON($url, $data = array(), $assert_state = ZiggeoException::HTTP_STATUS_OK) {
        return json_decode($this->get($url, $data, $assert_state));
    }

    function post($url, $data = array(), $assert_states = array(ZiggeoException::HTTP_STATUS_OK, ZiggeoException::HTTP_STATUS_CREATED)) {
        $curl = $this->curl($url);
        curl_setopt($curl, CURLOPT_POST, true);
        foreach ($data as $key=>$value) {
            if (is_array($value))
                $data[$key] = json_encode($value);
        }
        if (@$data["file"] && class_exists("CurlFile"))
            $data["file"] = new \CURLFile(str_replace("@", "", $data["file"]), "video/mp4", "video.mp4");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        $this->assert_state($assert_states, curl_getinfo($curl, CURLINFO_HTTP_CODE), $result);
        return $result;
    }

    function postJSON($url, $data = array(), $assert_states = array(ZiggeoException::HTTP_STATUS_OK, ZiggeoException::HTTP_STATUS_CREATED)) {
        return json_decode($this->post($url, $data, $assert_states));
    }

    function delete($url, $assert_state = ZiggeoException::HTTP_STATUS_OK) {
        $curl = $this->curl($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        $result = curl_exec($curl);
        $this->assert_state($assert_state, curl_getinfo($curl, CURLINFO_HTTP_CODE), $result);
        return $result;
    }

    function deleteJSON($url, $assert_state = ZiggeoException::HTTP_STATUS_OK) {
        return json_decode($this->delete($url, $assert_state));
    }
}
