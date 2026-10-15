<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostContentLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $category = Category::create(['name' => '雑記']);
        $this->post = Post::create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'title' => 'タイトル',
            'content' => 'もとの本文',
        ]);
    }

    private function update(string $content)
    {
        return $this->actingAs($this->user)->put(route('posts.update', $this->post), [
            'title' => 'タイトル',
            'category_id' => $this->post->category_id,
            'content' => $content,
        ]);
    }

    public function test_140文字ちょうどの本文は更新できる(): void
    {
        $content = str_repeat('あ', 140);

        $this->update($content)->assertRedirect(route('posts.index'));

        $this->assertSame($content, $this->post->fresh()->content);
    }

    public function test_141文字の本文は日本語のエラーになり保存されない(): void
    {
        $this->update(str_repeat('あ', 141))
            ->assertSessionHasErrors(['content' => '本文は140文字以内で入力してください']);

        $this->assertSame('もとの本文', $this->post->fresh()->content);
    }

    public function test_編集画面に残り文字数の表示がある(): void
    {
        $this->actingAs($this->user)
            ->get(route('posts.edit', $this->post))
            ->assertOk()
            ->assertSee('id="content-counter"', false)
            ->assertSee('const MAX_LENGTH = 140;', false);
    }

    public function test_本文が空ならエラーになる(): void
    {
        $this->update('')
            ->assertSessionHasErrors(['content' => '本文を入力してください']);

        $this->assertSame('もとの本文', $this->post->fresh()->content);
    }
}
