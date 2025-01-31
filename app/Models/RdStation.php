<?php

namespace App\Models;

class RdStation{

    private $name;
    private $email;
    private $job_title;
    private $state;
    private $city;
    private $country;
    private $personal_phone;
    private $twitter;
    private $facebook;
    private $linkedin;
    private $website;
    private $cf_custom_field_api_identifier;
    private $company_name;
    private $company_site;
    private $company_address;
    private $client_tracking_id;
    private $traffic_source;
    private $traffic_medium;
    private $traffic_campaign;
    private $traffic_value;

    public function setName($name){
        $this->name = $name;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setJobTitle($job_title){
        $this->job_title = $job_title;
    }
    public function setState($state){
        $this->state = $state;
    }
    public function setCity($city){
        $this->city = $city;
    }
    public function setCountry($country){
        $this->country = $country;
    }
    public function setPersonalPhone($personal_phone){
        $this->personal_phone = $personal_phone;
    }
    public function setTwitter($twitter){
        $this->twitter = $twitter;
    }
    public function setFacebook($facebook){
        $this->facebook = $facebook;
    }
    public function setLinkedin($linkedin){
        $this->linkedin = $linkedin;
    }
    public function setWebsite($website){
        $this->website = $website;
    }
    public function setCfCustomFieldApiIdentifier($cf_custom_field_api_identifier){
        $this->cf_custom_field_api_identifier = $cf_custom_field_api_identifier;
    }
    public function setCompanyName($company_name){
        $this->company_name = $company_name;
    }
    public function setCompanySite($company_site){
        $this->company_site = $company_site;
    }
    public function setCompanyAddress($company_address){
        $this->company_address = $company_address;
    }
    public function setClientTrackingId($client_tracking_id){
        $this->client_tracking_id = $client_tracking_id;
    }
    public function setTrafficSource($traffic_source){
        $this->traffic_source = $traffic_source;
    }
    public function setTrafficMedium($traffic_medium){
        $this->traffic_medium = $traffic_medium;
    }
    public function setTrafficCampaign($traffic_campaign){
        $this->traffic_campaign = $traffic_campaign;
    }
    public function setTrafficValue($traffic_value){
        $this->traffic_value = $traffic_value;
    }



    public function getName(){
        return $this->name;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getJobTitle(){
        return $this->job_title;
    }
    public function getState(){
        return $this->state;
    }
    public function getCity(){
        return $this->city;
    }
    public function getCountry(){
        return $this->country;
    }
    public function getPersonalPhone(){
        return $this->personal_phone;
    }
    public function getTwitter(){
        return $this->twitter;
    }
    public function getFacebook(){
        return $this->facebook;
    }
    public function getLinkedin(){
        return $this->linkedin;
    }
    public function getWebsite(){
        return $this->website;
    }
    public function getCfCustomFieldApiIdentifier(){
        return $this->cf_custom_field_api_identifier;
    }
    public function getCompanyName(){
        return $this->company_name;
    }
    public function getCompanySite(){
        return $this->company_site;
    }
    public function getCompanyAddress(){
        return $this->company_address;
    }
    public function getClientTrackingId(){
        return $this->client_tracking_id;
    }
    public function getTrafficSource(){
        return $this->traffic_source;
    }
    public function getTrafficMedium(){
        return $this->traffic_medium;
    }
    public function getTrafficCampaign(){
        return $this->traffic_campaign;
    }
    public function getTrafficValue(){
        return $this->traffic_value;
    }


    public function getToken(){
        $url = "https://api.rd.services/auth/token";
        $data = array(
            'client_id' => "d4dfdbd4-c13e-4311-9e23-9a486ecfbd7c",
            'client_secret' => "e71dfced44f24bff9cb00c3e65290214",
            'code' => "2448903dbb63af0bafcf012a29072d70"
        );
        return $this->jsonPOST2($url, $data);
    }

    function jsonPOST2($url, $data){
        $content = json_encode($data);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 ) {
            die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }
        curl_close($curl);
        $response = json_decode($json_response, true);
        return $response;
    }

    public function updateToken(){
        $refresh_token = $this->getToken()["refresh_token"];


        $url = "https://api.rd.services/auth/token";
        $data = array(
            'client_id' => "d4dfdbd4-c13e-4311-9e23-9a486ecfbd7c",
            'client_secret' => "e71dfced44f24bff9cb00c3e65290214",
            'refresh_token' => $refresh_token
        );
        return $this->jsonPOST2($url, $data);
    }



    public function send($data){
        $api_key = "avGgUicFJRIGdtMwcwPcYECRhnITXMjsyCgB";
        $url = "https://api.rd.services/platform/conversions?api_key=".$api_key;
        return $this->jwt_request_post($data, $url);
    }


    function jwt_request_post($post, $url) {
       $ch = curl_init($url); // INICIALIZA cURL
       $post = json_encode($post); // CODIFICA EL ARRAY EN UNA CADENA JSON
       //$authorization = "Authorization: Bearer ".$token; // PREPARA LA AUTORIZACION DEL TOKEN
       curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // INYECTA EL TOKEN EN EL HEADER
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //RETORNA LOS DATOS DE SALIDA COMO UNA CADENA, EN LUGAR DE DATOS SIN PROCESAR
       curl_setopt($ch, CURLOPT_POST, 1); // ESPECIFICA EL METODO DE LA SOLICITUD - POST
       curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
       $result = curl_exec($ch); // EJECUTA LA SENTENCIA cURL
       curl_close($ch); // CIERRA LA CONEXION cURL
       return json_decode($result); // RETORNA LOS DATOS RECIBIDOS
    }

    public function sendFromCrm($data){
        $api_key = "avGgUicFJRIGdtMwcwPcYECRhnITXMjsyCgB";
        $url = "https://api.rd.services/platform/conversions?api_key=".$api_key;
        return $this->jwtRequestPost($data, $url);
    }

    function jwtRequestPost($post, $url) {
       $ch = curl_init($url); // INICIALIZA cURL
       $post = json_encode($post); // CODIFICA EL ARRAY EN UNA CADENA JSON
       //$authorization = "Authorization: Bearer ".$token; // PREPARA LA AUTORIZACION DEL TOKEN
       curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // INYECTA EL TOKEN EN EL HEADER
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //RETORNA LOS DATOS DE SALIDA COMO UNA CADENA, EN LUGAR DE DATOS SIN PROCESAR
       curl_setopt($ch, CURLOPT_POST, 1); // ESPECIFICA EL METODO DE LA SOLICITUD - POST
       curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
       $result = curl_exec($ch); // EJECUTA LA SENTENCIA cURL
       curl_close($ch); // CIERRA LA CONEXION cURL
       
    }

}