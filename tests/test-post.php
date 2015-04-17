<?php

/**
 * Class PostTest
 *
 * This code relies on the WP_UnitTestCase object, which is more complex to setup than PHPUnit
 *
 * Follow these instructions to setup a testable WP environment:
 *
 * http://webdevstudios.com/2014/08/07/unit-testing-your-plugins/
 * http://codesymphony.co/writing-wordpress-plugin-unit-tests/
 *
 */
class PostTest extends WP_UnitTestCase
{

    /**
     * Test save method by saving post and verify id is returned
     *
     * @dataProvider dataProvider
     */
    public function testSave($data)
    {
        $post_id = $this->_saveHelper($data);

        $this->assertNotEmpty($post_id);
    }

    /**
     * Test load method by saving post, and reading it back
     *
     * @dataProvider dataProvider
     */
    public function testLoad($data)
    {
        $post_id = $this->_saveHelper($data);
        $this->assertNotEmpty($post_id);

        $post = new Post($post_id);

        foreach ($data as $key => $value)
            $this->assertEquals($data[$key], $post->get($key));
    }

    /**
     * Test set method by saving post, making changes, and reading back
     *
     * @dataProvider dataProvider
     */
    public function testSet($data)
    {
        $post_id = $this->_saveHelper($data);

        $post = new Post($post_id);

        $post->set('post_title', 'new title');
        $post->set('meta1', 'new meta');
        $post->set('meta3', 'meta 3');
        $post->save();

        $post = new Post($post_id);

        $this->assertEquals($post->get('post_title'), 'new title');
        $this->assertEquals($post->get('meta1'), 'new meta');
        $this->assertEquals($post->get('meta3'), 'meta 3');
    }

    /**
     * Test object access magic methods by saving post, making changes, and reading back
     *
     * @dataProvider dataProvider
     */
    public function testObjectAccess($data)
    {
        $post_id = $this->_saveHelper($data);

        $post = new Post($post_id);

        $post->post_title = 'new title';
        $post->meta1 = 'new meta';
        $post->meta3 = 'meta 3';
        $post->meta_object = new WP();

        $post->save();

        $post = new Post($post_id);

        $this->assertEquals($post->post_title, 'new title');
        $this->assertEquals($post->meta1, 'new meta');
        $this->assertEquals($post->meta3, 'meta 3');
        $this->assertEquals($post->meta_object, new WP());

    }

    /**
     * Support method to save the post from the data provided
     *
     * @param $data
     * @return integer
     */
    public function _saveHelper($data)
    {
        $post = new Post();

        foreach ($data as $key => $value)
            $post->set($key, $value);

        return $post->save();
    }

    /**
     * Test these values
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(array('post_title' => 'title', 'post_content' => 'content', 'meta1' => 'meta1', 'meta2' => 10)),
            array(array('post_title' => 'title', 'post_content' => 'content', 'meta1' => 'meta1', 'meta2' => array(1, 2, 3))),
            array(array('post_title' => 'title', 'post_content' => 'content', 'meta1' => 'meta1', 'meta2' => new WP())),
            array(array('post_type' => 'page', 'post_title' => 'title', 'post_content' => 'content', 'meta1' => 'meta1', 'meta2' => 10)),
            array(array('post_type' => 'custom', 'post_title' => 'title', 'post_content' => 'content', 'meta1' => 'meta1', 'meta2' => 10)),
        );
    }


}

