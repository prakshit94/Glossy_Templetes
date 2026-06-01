<?php

namespace Tests\Feature\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\Member;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_direct_conversation_and_send_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $conversationResponse = $this->actingAs($sender)
            ->postJson('/chat/api/conversations', [
                'type' => 'direct',
                'user_id' => $recipient->id,
            ]);

        $conversationResponse->assertCreated();
        $conversationId = $conversationResponse->json('data.id');

        $messageResponse = $this->actingAs($sender)
            ->postJson("/chat/api/conversations/{$conversationId}/messages", [
                'type' => 'text',
                'content' => 'Hello from the isolated chat module.',
            ]);

        $messageResponse->assertCreated()
            ->assertJsonPath('data.content', 'Hello from the isolated chat module.');

        $this->assertDatabaseHas('chat_conversations', ['id' => $conversationId, 'type' => 'direct']);
        $this->assertDatabaseHas('chat_messages', ['conversation_id' => $conversationId, 'sender_id' => $sender->id]);
    }

    public function test_non_member_cannot_read_messages(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::create([
            'type' => 'direct',
            'privacy' => 'private',
            'created_by' => $owner->id,
        ]);
        Member::create([
            'conversation_id' => $conversation->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'type' => 'text',
            'content' => 'Private history',
        ]);

        $this->actingAs($outsider)
            ->getJson("/chat/api/conversations/{$conversation->id}/messages")
            ->assertNotFound();
    }
}
