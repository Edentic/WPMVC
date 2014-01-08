<?php
/**
 * ::: Custom Post Model :::
 * Represents a single custom post
 */

namespace plugins\WPMVC\Core\model;

abstract class CustomPostModel {
    protected  $ID;
    static protected $__CLASS__ = __CLASS__;

    /**
     * Name of the custom post
     * @var string
     */
    static protected $customPostName = '';

    /**
     * Features the custom post type supports
     * @var array
     */
    static protected $supports = array(
            'title',
            'editor',
            'revisions'
        );

    /**
     * Options for the custom post type
     * @var array
     */
    static protected $customPostOptions = array(
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true
    );

    /**
     * Custom post type variables that should not be saved!
     * @var array
     */
    protected $doNotSaveVars = array();

    //Post fields
    protected $postTitle;
    protected $postContent;
    protected $postDate;
    protected $postStatus;
    protected $taxInput;
    protected $tags;



    public function __construct() {
        $this->doNotSaveVars[] = "ID";
        $this->doNotSaveVars[] = "customPostName";
        $this->doNotSaveVars[] = "doNotSaveVars";
        $this->doNotSaveVars[] = "customPostOptions";
        $this->doNotSaveVars[] = "postTitle";
        $this->doNotSaveVars[] = "postContent";
        $this->doNotSaveVars[] = "postDate";
        $this->doNotSaveVars[] = "postStatus";
        $this->doNotSaveVars[] = "taxInput";
        $this->doNotSaveVars[] = "tags";


        $this->postStatus = 'publish';
        $this->postDate = new \DateTime('now', new \DateTimeZone('Europe/Copenhagen'));
        $this->tags = array();
        $this->taxInput = array();

        $this->createCustomPostType();
    }

    /**
     * Returns ID of post type
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }


    /**
     * Loads new post from db
     *
     * @param $post_id
     * @return bool
     * @throws \Exception
     */
    public function load($post_id) {
        $post = get_post($post_id);
        if(!$post) {
            return false;
        }

        if($post->post_type !== strtolower(static::$customPostName)) {
            return false;
        }

        $this->ID = $post->ID;
        $this->postDate = new \DateTime($post->post_date, new \DateTimeZone('Europe/Copenhagen'));
        $this->postContent = $post->post_content;
        $this->postTitle = $post->post_title;
        $this->postStatus = $post->post_status;
        $this->customPostName = $post->post_type;

        $metaData = get_post_meta($this->ID);

        foreach($metaData as $key => $value) {
            if(is_array($value) && count($value) <= 1) {
                $value = $value[0];
            }

            if(($value == serialize(false) || @unserialize($value) !== false)) {
                $value = unserialize($value);
            }

            $this->$key = $value;
        }

        return true;
    }

    private static function classTest() {
        if(__CLASS__ == static::$__CLASS__) {
            throw new \Exception('A static protected $__CLASS__ = __CLASS__ has to be declared in your custom post model!');
        }
    }

    /**
     * Returns all posts of this post type
     * @param $post_per_page
     * @param int $page
     * @param string $orderBy
     * @param string $order
     * @param string $post_status
     * @return mixed
     */
    public static function all($post_per_page = -1, $page = 0, $orderBy = 'post_date', $order = 'DESC', $post_status = 'publish') {
        self::classTest();

        $offset = $post_per_page * $page;
        $posts = get_posts(array(
            'post_type' => static::$customPostName,
            'posts_per_page' => $post_per_page,
            'offset' => $offset,
            'order_by' => $orderBy,
            'order' => $order,
            'post_status' => $post_status,
            'numberposts' => $post_per_page
        ));

        $o = array();
        foreach($posts as $post) {
            $class = static::$__CLASS__;
            $obj = new $class;
            $obj->load($post->ID);
            $o[] = $obj;
        }

        return $o;
    }

    /**
     * Returns post from specific query
     * @param array $query
     * @return mixed
     */
    public static function where(Array $query) {
        self::classTest();
        $inpQuery = array_merge($query, array('post_type' => static::$customPostName, 'posts_per_page' => -1));
        $posts = get_posts($inpQuery);

        $o = array();
        foreach($posts as $post) {
            $class = static::$__CLASS__;
            $obj = new $class;
            $obj->load($post->ID);
            $o[] = $obj;
        }

        return $o;
    }

    /**
     * Saves post to database
     */
    public function save($doPostUpdate = false) {
        if($doPostUpdate == true) {
            $inputArray = array(
                'post_title' => $this->postTitle,
                'post_content' => $this->postContent,
                'post_date' => $this->postDate->format('Y-m-d H:i:s'),
                'post_status' => $this->postStatus,
                'tax_input' => $this->taxInput,
                'tags_input' => $this->tags,
                'post_type' => static::$customPostName
            );

            if(!$this->ID) {
                $this->ID = $this->createPost($inputArray);
            } else {
                $this->updatePost($inputArray);
            }
        }

        $this->updateMeta();
    }

    /**
     * Deletes post from database
     * @return bool
     * @throws \Exception
     */
    public function delete() {
        if(!$this->ID) {
            throw new \Exception('No post has been loaded into model!');
        }

       if(wp_delete_post($this->ID, true) === false) {
            throw new \Exception('Post could not be deleted!');
       }

        return true;
    }

    /**
     * Creates a new post and updates its ID
     *
     * @param array $inputArray
     * @throws \Exception
     * @return int|\WP_Error
     */
    private function createPost(Array $inputArray) {
        $postID = wp_insert_post($inputArray, true);
        if($postID instanceof \WP_Error) {
            throw new \Exception('Post could not be inserted!');
        };

        return $postID;
    }

    /**
     * Updates post in db
     * @param array $inputArray
     * @return int|\WP_Error
     * @throws \Exception
     */
    private function updatePost(Array $inputArray) {
        $inputArray['ID'] = $this->ID;
        $postID = \wp_update_post($inputArray, true);
        if($postID instanceof \WP_Error) {
            throw new \Exception('Post could not be inserted!');
        };

        return $postID;
    }

    /**
     * Updates meta fields and creates new ones
     */
    private function updateMeta() {
        $vars = $this->getVars();

        foreach($vars as $fieldname => $value) {
            (!get_post_meta($this->ID, $fieldname)) ? add_post_meta($this->ID, $fieldname, $value, true) : update_post_meta($this->ID, $fieldname, $value);
        }
    }

    /**
     * Returns a filtered list of object variables
     * @return array
     */
    private function getVars() {
        $vars = get_object_vars($this);
        $o = array();
        foreach($vars as $key => $value) {
            if(!in_array($key, $this->doNotSaveVars)) $o[$key] = $value;
        }

        return $o;
    }

    /**
     * Registers a new custom post type with given name
     * @throws \Exception
     */
    public static function createCustomPostType() {
        self::classTest();

        if(!static::$customPostName) throw new \Exception('Custom post type name has to be given');
        if(get_post_type_object(static::$customPostName) != false) {
            return false;
        };

        $options = static::$customPostOptions;
        if(!isset($options['labels'])) {
            $options['label'] = static::$customPostName;
        }

        if(!isset($options['supports'])) {
            $options['supports'] = static::$supports;
        }

       if(register_post_type(static::$customPostName, $options) instanceof \WP_Error) {
           throw new \Exception('Post type could not be created!');
       };

        return true;
    }

} 