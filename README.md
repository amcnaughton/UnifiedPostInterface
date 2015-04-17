# Unified Interface for WordPress post and post meta data

Make working with WordPress posts and metadata just a little less bit ugly. The code cleanly supports
scalars, arrays, and objects as meta data values. It's also optimized to only save changed data.

Here's the traditional WordPress way of dealing with a post and its meta data

     $post = get_post(id);
     $meta1 = get_post_meta($id, 'meta1', true);
     $meta2 = get_post_meta($id, 'meta2', true);
     if($meta1 == $meta2)
         update_post_meta($id, 'meta3', 'value');
     $post->post_title = 'something new';
     wp_update_post($post);

That's a lot of ugly code. Here's the new way:

     $post = new Post($id);
     if($post->meta1 == $post->meta2)
         $post->meta3 = 'value';
     $post->post_title = 'something new';
     $post->save();

Or it can be written as:

     $post = new Post($id);
     if($post->get('meta1') == $post->get('meta2')
         $post->set('meta3', 'value');
     $post->set('post_title', 'something new');
     $post->save();

You can also create a new post:

     $post = new Post();
     $post->post_title = 'something new';
     $post->meta3 = 'value';
     $id = $post->save();
     
#####To run the accompanying PHPUnit test cases you have to setup WP_UnitTestCase, which is more complex to setup than PHPUnit.

Follow these instructions to setup a testable WP environment:
 * http://webdevstudios.com/2014/08/07/unit-testing-your-plugins/
 * http://codesymphony.co/writing-wordpress-plugin-unit-tests/