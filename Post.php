<?php

/**
 * Created by PhpStorm.
 * User: Allan McNaughton
 * Date: 4/16/2015
 * Time: 3:11 PM
 *
 * Make working with WordPress posts and metadata just a little less bit ugly. The code cleanly supports
 * scalars, arrays, and objects as meta data values. It's also optimized to only save changed data.
 *
 * Here's the traditional WordPress way of dealing with a post and its meta data
 *
 *      $post = get_post(id);
 *      $meta1 = get_post_meta($id, 'meta1', true);
 *      $meta2 = get_post_meta($id, 'meta2', true);
 *      if($meta1 == $meta2)
 *          update_post_meta($id, 'meta3', 'value');
 *      $post->post_title = 'something new';
 *      wp_update_post($post);
 *
 * That's a lot of ugly code. Here's the new way:
 *
 *      $post = new Post($id);
 *      if($post->meta1 == $post->meta2)
 *          $post->meta3 = 'value';
 *      $post->post_title = 'something new';
 *      $post->save();
 *
 * Or it can be written as:
 *
 *      $post = new Post($id);
 *      if($post->get('meta1') == $post->get('meta2')
 *          $post->set('meta3', 'value');
 *      $post->set('post_title', 'something new');
 *      $post->save();
 *
 * You can also create a new post:
 *
 *      $post = new Post();
 *      $post->post_title = 'something new';
 *      $post->meta3 = 'value';
 *      $id = $post->save();
 */
class Post implements PostInterface
{
    protected $dirty;
    protected $post;
    protected $post_meta;

    // fields other than these are stored as post meta data
    private $wp_post_fields = array(
        "ID",
        "post_author",
        "post_date",
        "post_date_gmt",
        "post_content",
        "post_title",
        "post_excerpt",
        "post_status",
        "comment_status",
        "ping_status",
        "post_password",
        "post_name",
        "to_ping",
        "pinged",
        "post_modified",
        "post_modified_gmt",
        "post_content_filtered",
        "post_parent",
        "guid",
        "menu_order",
        "post_type",
        "post_mime_type",
        "comment_count"
    );

    public function __construct($id = 0)
    {
        if ($id)
            $this->load($id);
    }

    /**
     * Load a post and its meta data
     *
     * @param int $id
     * @return $this
     * @throws Exception
     */
    protected function load($id = 0)
    {
        $this->post = get_post($id, ARRAY_A);

        if (empty($this->post['ID'])) {
            throw new Exception("Invalid post id $id");
        }

        // retrieve and normalize post meta data
        $post_meta = get_post_meta($id);

        if (!empty($post_meta)) {

            foreach ($post_meta as $key => $value) {
                if ($this->is_post_field($key))
                    throw new Exception("Meta key $key conflicts with a valid WP Post field");

                $value = $value[0];
                if (is_serialized($value))
                    $this->post_meta[$key] = unserialize($value);
                else
                    $this->post_meta[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Create a new post or update an existing one
     *
     * @return $this
     * @throws Exception
     */
    public function save()
    {
        $this->save_post();
        $this->save_post_meta();
        $this->dirty = [];

        return $this->post['ID'];
    }

    /**
     * Helper method to save the WP_Post object
     *
     * @throws Exception
     */
    protected function save_post()
    {
        // do we need to save the WP post?
        $save_wp_post = false;
        foreach ($this->dirty as $key => $value) {

            if ($this->is_post_field($key)) {
                $save_wp_post = true;
                break;
            }
        }

        // save/update the WP post if dirty
        if ($save_wp_post) {
            if (empty($this->post['ID'])) {

                $post_id = wp_insert_post($this->post);
                $this->post['ID'] = $post_id;

            } else
                $post_id = wp_update_post($this->post);

            if (!$post_id)
                throw new Exception("Error saving post $post_id");
        }
    }

    /**
     * Helper method to save the post meta data
     */
    protected function save_post_meta()
    {
        foreach ($this->dirty as $key => $value) {

            if ($this->is_post_meta($key)) {

                update_post_meta($this->post['ID'], $key, $this->post_meta[$key]);
            }
        }
    }

    /**
     * Is this a WP_Post field?
     *
     * @param $key
     * @return bool
     */
    protected function is_post_field($key)
    {
        return in_array($key, $this->wp_post_fields);
    }

    /**
     * Is this post meta data?
     *
     * @param $key
     * @return bool
     */
    protected function is_post_meta($key)
    {
        return !$this->is_post_field($key);
    }

    /**
     * Delete the post
     *
     * @throws Exception
     */
    public function delete()
    {
        if (empty($this->post['ID']))
            throw new Exception("Missing ID, cannot delete");

        wp_delete_post($this->post['ID'], true);
    }

    /**
     * Get a post or post meta data value
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if ($this->is_post_meta($key))
            return $this->post_meta[$key];
        else
            return $this->post[$key];
    }

    /**
     * Update a post key value and mark the dirty attribute
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        if ($this->is_post_meta($key))
            $this->post_meta[$key] = $value;
        else
            $this->post[$key] = $value;

        $this->dirty[$key] = true;

        return $this;
    }

    /**
     * Magic method to allow get access as an object
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic method to allow set access as an object
     *
     * @param $key
     * @param $value
     *  @return mixed
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }
}