<?php

namespace Redmine;

/**
 * Simple PHP Redmine client
 * based on the original class publish Thomas Spycher : http://tspycher.com/2011/03/using-the-redmine-api-with-php/
 *
 * Eventually it should look like the brillant php-github-api by Ornicar! :)
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 *
 * Website: http://github.com/kbsali/php-redmine-api
 */
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

    /**
     * Loads users, trackers, statuses, projects
     * and issue categories if $project is provided
     * @param  string $project name of the project
     */
    public function init($project = null) {
        $this->users = $this->listUsers();
        $this->trackers = $this->listTrackers();
        $this->statuses = $this->listStatuses();
        $this->projects = $this->listProjects();
        if (null !== $project) {
            $this->categories = $this->listIssueCategories($project);
        }
    }


    /**
     * Returns an array of users with login/id pairs
     * @return array
     */
    public function listUsers() {
        $arr = $this->getUsers();
        $ret = array();
        foreach($arr as $e) {
            $ret[(string)$e->login] = (int)$e->id;
        }
        return $ret;
    }

    /**
     * Returns an array of trackers with name/id pairs
     * @return array
     */
    public function listTrackers() {
        $arr = $this->getTrackers();
        $ret = array();
        foreach($arr as $e) {
            $ret[(string)$e->name] = (int)$e->id;
        }
        return $ret;
    }

    /**
     * Returns an array of statuses with name/id pairs
     * @return array
     */
    public function listStatuses() {
        $arr = $this->getStatuses();
        $ret = array();
        foreach($arr as $e) {
            $ret[(string)$e->name] = (int)$e->id;
        }
        return $ret;
    }

    /**
     * Returns an array of projects with name/id pairs
     * @return array
     */
    public function listProjects() {
        $arr = $this->getProjects();
        $ret = array();
        foreach($arr as $e) {
            $ret[(string)$e->name] = (int)$e->id;
        }
        return $ret;
    }

    /**
     * Returns an array of categories with name/id pairs
     * @return array
     */
    public function listIssueCategories($project) {
        $arr = $this->getIssueCategories($project);
        $ret = array();
        foreach($arr as $e) {
            $ret[(string)$e->name] = (int)$e->id;
        }
        return $ret;
    }



    /**
     * Returns the id of a project given its name
     * @param string $project
     * @return false|int
     */
    public function getProjectId($project) {
        if(!is_array($this->projects) || 0 === count($this->projects)) {
            $this->projects = $this->listProjects();
        }
        if(!isset($this->projects[$project])) {
            return false;
        }
        return $this->projects[(string)$project];
    }

    /**
     * Returns the id of a user given its username
     * @param string $username
     * @return false|int
     */
    public function getUserId($username) {
        if(!is_array($this->users) || 0 === count($this->users)) {
            $this->users = $this->listUsers();
        }
        if(!isset($this->users[$username])) {
            return false;
        }
        return $this->users[(string)$username];
    }

    /**
     * Returns the id of a status given its name
     * @param string $status
     * @return int
     */
    public function getStatusId($status) {
        if(!is_array($this->statuses) || 0 === count($this->statuses)) {
            $this->statuses = $this->listStatuses();
        }
        if(!isset($this->statuses[$status])) {
            return 1;
        }
        return $this->statuses[(string)$status];
    }

    /**
     * Returns the id of a tracker given its name
     * @param string $tracker
     * @return int
     */
    public function getTrackerId($tracker) {
        if(!is_array($this->trackers) || 0 === count($this->trackers)) {
            $this->trackers = $this->listTrackers();
        }
        if(!isset($this->trackers[$tracker])) {
            return 1;
        }
        return $this->trackers[(string)$tracker];
    }

    /**
     * Returns the id of a category given its name
     * @param string $category
     * @return int
     */
    public function getIssueCategoryId($category) {
        if(!is_array($this->categories) || 0 === count($this->categories)) {
            return 1;
        }
        if(!isset($this->categories[$category])) {
            return 1;
        }
        return $this->categories[(string)$category];
    }


    /**
     * @param mixed $restUrl
     * @param string $method. (default: 'GET')
     * @param string $data. (default: "")
     * @return false|SimpleXMLElement
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
            case 'post':
                curl_setopt($this->curl, CURLOPT_POST, 1);
                if(isset($data)) {curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case 'put':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if(isset($data)) {curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case 'delete':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
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
     * @return false|SimpleXMLElement
     */
    public function getUsers() {
        return $this->runRequest('/users.xml', 'GET');
    }

    /**
     * @return false|SimpleXMLElement
     */
    public function getStatuses() {
        return $this->runRequest('/issue_statuses.xml', 'GET');
    }

    /**
     * @return false|SimpleXMLElement
     */
    public function getTrackers() {
        return $this->runRequest('/trackers.xml', 'GET');
    }

    /**
     * @return false|SimpleXMLElement
     */
    public function getProjects() {
        return $this->runRequest('/projects.xml', 'GET');
    }

    /**
     * @return false|SimpleXMLElement
     */
    public function getIssues() {
        // @todo implement filters!
        // $filters = array('project_id', 'tracker_id', 'assigned_to_id', 'status_id', 'query_id', 'offset', 'limit', 'created_on'); // cf_*
        return $this->runRequest('/issues.xml', 'GET');
    }

    /**
     * @param mixed $projectId
     * @return false|SimpleXMLElement
     */
    public function getIssueCategories($project) {
        return $this->runRequest('/projects/'.$project.'/issue_categories.xml', 'GET');
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