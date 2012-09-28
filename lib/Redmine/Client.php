<?php

namespace Redmine;

class Client {

    const PRIO_LOW       = 1;
    const PRIO_NORMAL    = 2;
    const PRIO_HIGH      = 3;
    const PRIO_URGENT    = 4;
    const PRIO_IMMEDIATE = 5;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $apikey;

    /**
     * @var Ressource
     */
    private $curl;

    /**
     * @var array
     */
    private $projects = array();

    /**
     * @var array
     */
    private $users = array();

    /**
     * @var array
     */
    private $trackers = array();

    /**
     * @var array
     */
    private $statuses = array();

    /**
     * @var array
     */
    private $categories = array();

    /**
     * @param string $url
     * @param string $apikey
     */
    public function __construct($url, $apikey) {
        $this->url    = $url;
        $this->apikey = $apikey;
    }

    public function init($project = null) {
        $this->listUsers();
        $this->listTrackers();
        $this->listStatuses();
        $this->listProjects();
        if (null !== $project) {
            $this->listIssueCategories($project);
        }
    }



    public function listUsers() {
        $arr = $this->getUsers();
        foreach($arr as $e) {
            $this->users[(string)$e->login] = (int)$e->id;
        }
    }

    public function listTrackers() {
        $arr = $this->getTrackers();
        foreach($arr as $e) {
            $this->trackers[(string)$e->name] = (int)$e->id;
        }
    }

    public function listStatuses() {
        $arr = $this->getStatuses();
        foreach($arr as $e) {
            $this->statuses[(string)$e->name] = (int)$e->id;
        }
    }

    public function listProjects() {
        $arr = $this->getProjects();
        foreach($arr as $e) {
            $this->projects[(string)$e->name] = (int)$e->id;
        }
    }

    public function listIssueCategories($project) {
        $arr = $this->getIssueCategories($project);
        foreach($arr as $e) {
            $this->categories[(string)$e->name] = (int)$e->id;
        }
    }



    /**
     * @param string $project
     * @return false|string
     */
    public function getProjectId($project) {
        if(!is_array($this->projects) || 0 === count($this->projects)) {
            $this->listProjects();
        }
        if(isset($this->projects[$project])) {
            return $this->projects[(string)$project];
        }
        return false;
    }

    /**
     * @param string $username
     * @return false|string
     */
    public function getUserId($username) {
        if(!is_array($this->users) || 0 === count($this->users)) {
            $this->listUsers();
        }
        if(isset($this->users[$username])) {
            return $this->users[(string)$username];
        }
        return false;
    }

    /**
     * @param string $status
     * @return false|string
     */
    public function getStatusId($status) {
        if(!is_array($this->statuses) || 0 === count($this->statuses)) {
            $this->listStatuses();
        }
        if(isset($this->statuses[$status])) {
            return $this->statuses[(string)$status];
        }
        return 1;
    }

    /**
     * @param string $tracker
     * @return false|string
     */
    public function getTrackerId($tracker) {
        if(!is_array($this->trackers) || 0 === count($this->trackers)) {
            $this->listTrackers();
        }
        if(isset($this->trackers[$tracker])) {
            return $this->trackers[(string)$tracker];
        }
        return 1;
    }

    /**
     * @param string $category
     * @return false|string
     */
    public function getIssueCategoryId($category) {
        if(!is_array($this->categories) || 0 === count($this->categories)) {
            return 1;
        }
        if(isset($this->categories[$category])) {
            return $this->categories[(string)$category];
        }
        return 1;
    }


    /**
     * @param mixed $restUrl
     * @param string $method. (default: 'GET')
     * @param string $data. (default: "")
     * @return void
     */
    private function runRequest($restUrl, $method = 'GET', $data = '') {
        $method = strtolower($method);
        $this->curl = curl_init();

        // Authentication
        if(isset($this->apikey)) {
            curl_setopt($this->curl, CURLOPT_USERPWD, $this->apikey.':'.rand(100000, 199999) );
            curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        curl_setopt($this->curl, CURLOPT_URL, $this->url.$restUrl);
        curl_setopt($this->curl, CURLOPT_PORT , 80);
        curl_setopt($this->curl, CURLOPT_VERBOSE, 0);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml",
            "Content-length: ".strlen($data)
        ));

        // Request
        switch ($method) {
            case "post":
                curl_setopt($this->curl, CURLOPT_POST, 1);
                if(isset($data)) {curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case "put":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if(isset($data)) {curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case "delete":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default: // get
                break;
        }
        // Run the request
        try {
            $response = curl_exec($this->curl);
            if(curl_errno($this->curl)) {
                curl_close($this->curl);
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        if($response) {
            if(substr($response, 0, 1) == '<') {
                return new \SimpleXMLElement($response);
            }
            return false;
        }
        return true;
    }

    /**
     * @return void
     */
    public function getUsers() {
        return $this->runRequest('/users.xml', 'GET', '');
    }

    /**
     * @return void
     */
    public function getStatuses() {
        return $this->runRequest('/issue_statuses.xml', 'GET', '');
    }

    /**
     * @return void
     */
    public function getTrackers() {
        return $this->runRequest('/trackers.xml', 'GET', '');
    }

    /**
     * @return void
     */
    public function getProjects() {
        return $this->runRequest('/projects.xml', 'GET', '');
    }

    /**
     * @param mixed $projectId
     * @return void
     */
    public function getIssues($projectId) {
        return $this->runRequest('/issues.xml'.$projectId, 'GET', '');
    }

    public function getIssueCategories($project) {
        return $this->runRequest('/projects/'.$project.'/issue_categories.xml', 'GET', '');
    }

    public function createIssue(array $params = array()) {

        $defaults = array(
            'project_id'     => 1,
            'project'        => null,

            'category_id'    => 1,
            'category'       => null,

            'priority_id'    => self::PRIO_NORMAL,

            'status_id'      => 1,
            'status'         => null,

            'tracker_id'     => 1,
            'tracker'        => null,

            'subject'        => null,
            'description'    => null,

            'assigned_to_id' => null,
            'author_id'      => null,
            'assigned_to'    => null,
            'author'         => null,

            'due_date'       => null,
            'start_date'     => date('Y-m-d'),
        );
        $params = array_merge($defaults, $params);
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

        if(null !== $params['project']) {
            $params['project_id'] = $this->getProjectId($params['project']);
        }
        if($params['project_id']) {
            $xml->addChild('project_id', $params['project_id']);
        }

        if(null !== $params['category']) {
            $params['category_id'] = $this->getIssueCategoryId($params['category']);
        }
        if($params['category_id']) {
            $xml->addChild('category_id', $params['category_id']);
        }

        $xml->addChild('priority_id', $params['priority_id']);

        if(null !== $params['status']) {
            $params['status_id'] = $this->getStatusId($params['status']);
        }
        if($params['status_id']) {
            $xml->addChild('status_id', $params['status_id']);
        }

        if(null !== $params['tracker']) {
            $params['tracker_id'] = $this->getTrackerId($params['tracker']);
        }
        if($params['tracker_id']) {
            $xml->addChild('tracker_id', $params['tracker_id']);
        }

        if(null !== $params['subject']) {
            $xml->addChild('subject', htmlspecialchars($params['subject']));
            // $xml->addChild('subject', htmlentities($params['subject']));
        }
        if(null !== $params['description']) {
            $xml->addChild('description', htmlspecialchars($params['description']));
            // $xml->addChild('description', htmlentities($params['description']));
        }

        if(null !== $params['assigned_to']) {
            $params['assigned_to_id'] = $this->getUserId($params['assigned_to']);
        }
        if($params['assigned_to_id']) {
            $xml->addChild('assigned_to_id', $params['assigned_to_id']);
        }
        if(null !== $params['author']) {
            $params['author_id'] = $this->getUserId($params['author']);
        }
        if($params['author_id']) {
            $xml->addChild('author_id', $params['author_id']);
        }

        if(null !== $params['due_date']) {
            $xml->addChild('due_date', $params['due_date']);
        }
        if(null !== $params['start_date']) {
            $xml->addChild('start_date', $params['start_date']);
        }

        return $this->runRequest('/issues.xml', 'POST', $xml->asXML() );
    }

    /**
     * @param mixed $status
     * @param mixed $issueId
     * @return void
     */
    public function setIssueStatus($issueId, $status) {
        $status_id = $this->getStatusId($status);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        $xml->addChild('id', $issueId);
        $xml->addChild('status_id', $status_id);
        return $this->runRequest('/issues/'.$issueId.'.xml', 'PUT', $xml->asXML() );
    }

    /**
     * @param mixed $issueId
     * @param mixed $note
     * @return void
     */
    public function addNoteToIssue($issueId, array $params = array()) {
        $defaults = array(
            'notes'     => null,
            'author_id' => null,
            'author'    => null,
        );
        $params = array_merge($defaults, $params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        $xml->addChild('id', $issueId);
        if(null !== $params['author']) {
            $params['author_id'] = $this->getUserId($params['author']);
        }
        if($params['author_id']) {
            $xml->addChild('author_id', $params['author_id']);
        }
        if(null !== $params['notes']) {
            $xml->addChild('notes', htmlspecialchars($params['notes']));
        }
        return $this->runRequest('/issues/'.$issueId.'.xml', 'PUT', $xml->asXML() );
    }
}