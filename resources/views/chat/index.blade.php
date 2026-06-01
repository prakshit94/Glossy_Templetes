<x-layouts.app pageTitle="Chat">
    <div
        class="h-[calc(100svh-5rem)] min-h-[720px] overflow-hidden bg-background"
        x-data="chatWorkspace({
            conversations: @js($conversations),
            users: @js($users),
            pollInterval: @js($pollInterval),
        })"
        x-init="init()"
    >
        <div class="grid h-full grid-cols-1 lg:grid-cols-[22rem_minmax(0,1fr)_18rem]">
            <aside class="flex min-h-0 flex-col border-r border-border/70 bg-card/40">
                <div class="border-b border-border/70 p-4">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-black tracking-tight text-foreground">Chat</h1>
                            <p class="text-xs font-semibold text-muted-foreground" x-text="onlineLabel"></p>
                        </div>
                        <button type="button" @click="showGroupModal = true" class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-background text-foreground shadow-sm transition hover:bg-accent" title="Create group">
                            <x-ui.icon name="plus" size="4" />
                        </button>
                    </div>

                    <label class="relative block">
                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                        <input
                            type="search"
                            x-model.debounce.250ms="search"
                            @input="runSearch"
                            placeholder="Search chats, people, messages"
                            class="h-11 w-full rounded-lg border border-border bg-background pl-9 pr-3 text-sm outline-none transition focus:border-primary/40 focus:ring-2 focus:ring-primary/15"
                        >
                    </label>
                </div>

                <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
                    <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground'" class="rounded-md px-3 py-1.5 text-xs font-bold">All</button>
                    <button type="button" @click="filter = 'unread'" :class="filter === 'unread' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground'" class="rounded-md px-3 py-1.5 text-xs font-bold">Unread</button>
                    <button type="button" @click="filter = 'pinned'" :class="filter === 'pinned' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground'" class="rounded-md px-3 py-1.5 text-xs font-bold">Pinned</button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <template x-if="searchResults.users.length">
                        <div class="border-b border-border/60 p-3">
                            <p class="mb-2 px-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground">People</p>
                            <template x-for="user in searchResults.users" :key="user.id">
                                <button type="button" @click="startDirect(user.id)" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-accent">
                                    <span class="relative flex size-9 items-center justify-center rounded-lg bg-secondary text-xs font-black text-secondary-foreground">
                                        <span x-text="initials(user.name)"></span>
                                        <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-card" :class="user.is_online ? 'bg-emerald-500' : 'bg-muted-foreground/40'"></span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-bold text-foreground" x-text="user.name"></span>
                                        <span class="block truncate text-xs text-muted-foreground" x-text="user.last_seen_label || user.location || user.email"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <template x-for="conversation in filteredConversations" :key="conversation.id">
                        <button type="button" @click="selectConversation(conversation)" class="flex w-full items-start gap-3 border-b border-border/50 px-4 py-3 text-left transition hover:bg-accent/70" :class="activeConversation?.id === conversation.id ? 'bg-accent' : ''">
                            <span class="relative flex size-11 items-center justify-center rounded-lg bg-secondary text-sm font-black text-secondary-foreground">
                                <span x-text="conversationInitials(conversation)"></span>
                                <span x-show="isOnline(conversation)" class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-card bg-emerald-500"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-black text-foreground" x-text="conversationTitle(conversation)"></span>
                                    <span class="shrink-0 text-[10px] font-semibold text-muted-foreground" x-text="lastTime(conversation)"></span>
                                </span>
                                <span class="mt-1 flex items-center justify-between gap-2">
                                    <span class="truncate text-xs text-muted-foreground" x-text="lastPreview(conversation)"></span>
                                    <span x-show="conversation.unread_count > 0" class="rounded-full bg-primary px-2 py-0.5 text-[10px] font-black text-primary-foreground" x-text="conversation.unread_count"></span>
                                </span>
                            </span>
                        </button>
                    </template>
                </div>
            </aside>

            <section class="flex min-h-0 flex-col bg-background">
                <template x-if="activeConversation">
                    <div class="flex min-h-0 flex-1 flex-col">
                        <header class="flex h-20 items-center justify-between gap-4 border-b border-border/70 px-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-11 items-center justify-center rounded-lg bg-secondary text-sm font-black text-secondary-foreground" x-text="conversationInitials(activeConversation)"></span>
                                <div class="min-w-0">
                                    <h2 class="truncate text-base font-black text-foreground" x-text="conversationTitle(activeConversation)"></h2>
                                    <p class="truncate text-xs font-semibold text-muted-foreground" x-text="conversationSubtitle(activeConversation)"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="togglePin" class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-accent" title="Pin chat">
                                    <x-ui.icon name="star" size="4" />
                                </button>
                                <button type="button" @click="toggleArchive" class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-accent" title="Archive chat">
                                    <x-ui.icon name="archive" size="4" />
                                </button>
                                <button type="button" x-show="activeConversation?.type === 'group'" @click="openGroupSettings" class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-accent" title="Group settings">
                                    <x-ui.icon name="settings" size="4" />
                                </button>
                            </div>
                        </header>

                        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-5" x-ref="messageScroller">
                            <template x-for="message in messages" :key="message.id">
                                <div class="flex" :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
                                    <div class="max-w-[78%]">
                                        <div class="mb-1 flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-muted-foreground" :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
                                            <span x-text="message.sender?.name || 'User'"></span>
                                            <span x-text="lastTime(message)"></span>
                                            <span x-show="message.edited_at">Edited</span>
                                        </div>
                                        <div class="rounded-lg border px-4 py-3 text-sm leading-relaxed shadow-sm" :class="message.sender_id === currentUserId ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-foreground'">
                                            <template x-if="message.parent">
                                                <button type="button" @click="scrollToMessage(message.parent_id)" class="mb-2 block w-full border-l-2 border-current/30 pl-2 text-left text-xs opacity-80 transition hover:opacity-100">
                                                    <span class="block truncate font-bold" x-text="message.parent.sender?.name || 'Reply'"></span>
                                                    <span class="block truncate" x-text="message.parent.content || 'Attachment'"></span>
                                                </button>
                                            </template>
                                            <template x-if="editingMessageId !== message.id">
                                                <p class="whitespace-pre-wrap break-words" x-text="message.content"></p>
                                            </template>
                                            <template x-if="editingMessageId === message.id">
                                                <div class="space-y-2">
                                                    <textarea
                                                        x-model="editingDraft"
                                                        rows="3"
                                                        class="w-full resize-none rounded-md border border-current/20 bg-background/95 px-3 py-2 text-sm text-foreground outline-none focus:ring-2 focus:ring-primary/20"
                                                    ></textarea>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" @click="cancelEdit" class="rounded-md px-2 py-1 text-[10px] font-black uppercase tracking-wider opacity-80 hover:opacity-100">Cancel</button>
                                                        <button type="button" @click="saveEdit(message)" class="rounded-md bg-background px-2 py-1 text-[10px] font-black uppercase tracking-wider text-foreground shadow-sm hover:opacity-90">Save</button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="message.attachments?.length">
                                                <div class="mt-2 space-y-1">
                                                    <template x-for="attachment in message.attachments" :key="attachment.path || attachment.name">
                                                        <div>
                                                            <template x-if="isImageAttachment(attachment)">
                                                                <a :href="attachment.path" target="_blank" class="block overflow-hidden rounded-md border border-current/20 bg-background/20">
                                                                    <img :src="attachment.path" :alt="attachment.name || 'Image attachment'" class="max-h-72 w-full object-cover">
                                                                </a>
                                                            </template>
                                                            <template x-if="!isImageAttachment(attachment)">
                                                                <a :href="attachment.path" target="_blank" class="flex items-center gap-2 rounded-md bg-background/20 px-2 py-2 text-xs font-bold underline">
                                                                    <x-ui.icon name="file-text" size="4" />
                                                                    <span class="min-w-0 flex-1 truncate" x-text="attachment.name || attachment.path"></span>
                                                                    <span class="shrink-0 no-underline opacity-70" x-text="formatFileSize(attachment.size)"></span>
                                                                </a>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-1 flex gap-2" :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
                                            <button type="button" @click="setReply(message)" class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground">Reply</button>
                                            <button type="button" @click="startEdit(message)" x-show="message.sender_id === currentUserId" class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground">Edit</button>
                                            <button type="button" @click="deleteMessage(message)" :disabled="busyMessageId === message.id" class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground hover:text-destructive disabled:opacity-50">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <form @submit.prevent="sendMessage" class="border-t border-border/70 bg-card/40 p-4">
                            <div x-show="errorMessage" x-cloak class="mb-3 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs font-semibold text-destructive" x-text="errorMessage"></div>
                            <div x-show="replyTo" class="mb-3 flex items-center justify-between rounded-lg border border-border bg-background px-3 py-2 text-xs">
                                <span class="truncate text-muted-foreground" x-text="replyTo ? 'Replying to ' + (replyTo.sender?.name || 'message') + ': ' + replyTo.content : ''"></span>
                                <button type="button" @click="replyTo = null" class="text-muted-foreground hover:text-foreground"><x-ui.icon name="x" size="4" /></button>
                            </div>
                            <div x-show="pendingAttachments.length" x-cloak class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="(file, index) in pendingAttachments" :key="`${file.name}-${file.size}-${index}`">
                                    <div class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-xs">
                                        <span class="flex size-8 items-center justify-center rounded-md bg-secondary text-secondary-foreground">
                                            <template x-if="file.type?.startsWith('image/')"><x-ui.icon name="image" size="4" /></template>
                                            <template x-if="!file.type?.startsWith('image/')"><x-ui.icon name="file-text" size="4" /></template>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-bold text-foreground" x-text="file.name"></span>
                                            <span class="block text-muted-foreground" x-text="formatFileSize(file.size)"></span>
                                        </span>
                                        <button type="button" @click="removePendingAttachment(index)" class="rounded-md p-1 text-muted-foreground hover:bg-accent hover:text-foreground">
                                            <x-ui.icon name="x" size="4" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-end gap-3">
                                <input x-ref="attachmentInput" type="file" multiple class="hidden" @change="handleAttachmentSelection" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.mp4,.mov,.webm,.mp3,.wav,.m4a,.ogg">
                                <button type="button" @click="$refs.attachmentInput.click()" class="inline-flex size-11 items-center justify-center rounded-lg border border-border bg-background text-muted-foreground transition hover:bg-accent hover:text-foreground" title="Attach files">
                                    <x-ui.icon name="paperclip" size="4" />
                                </button>
                                <textarea
                                    x-model="draft"
                                    @input="sendTyping"
                                    x-ref="composer"
                                    rows="1"
                                    placeholder="Message"
                                    class="max-h-32 min-h-11 flex-1 resize-none rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none transition focus:border-primary/40 focus:ring-2 focus:ring-primary/15"
                                ></textarea>
                                <button type="submit" :disabled="sendingMessage" class="inline-flex h-11 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-black text-primary-foreground shadow-sm transition hover:opacity-90 disabled:opacity-50">
                                    <x-ui.icon name="send" size="4" />
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                <template x-if="!activeConversation">
                    <div class="flex flex-1 items-center justify-center p-8">
                        <div class="text-center">
                            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-lg bg-secondary text-secondary-foreground">
                                <x-ui.icon name="mail" size="7" />
                            </div>
                            <h2 class="text-lg font-black text-foreground">Select a conversation</h2>
                        </div>
                    </div>
                </template>
            </section>

            <aside class="hidden min-h-0 flex-col border-l border-border/70 bg-card/40 lg:flex">
                <div class="space-y-3 border-b border-border/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-muted-foreground">Live Users</h2>
                            <p class="mt-1 text-xs font-semibold text-muted-foreground" x-text="`${onlineUsers.length} active, ${users.length} available`"></p>
                        </div>
                        <button type="button" @click="fetchUsers" class="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-background transition hover:bg-accent" title="Refresh users">
                            <x-ui.icon name="refresh-cw" size="4" />
                        </button>
                    </div>
                    <label class="relative block">
                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                        <input
                            type="search"
                            x-model.debounce.300ms="userSearch"
                            @input="fetchUsers"
                            placeholder="Find users"
                            class="h-10 w-full rounded-lg border border-border bg-background pl-9 pr-3 text-sm outline-none transition focus:border-primary/40 focus:ring-2 focus:ring-primary/15"
                        >
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="userFilter = 'all'" :class="userFilter === 'all' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground'" class="rounded-md px-3 py-1.5 text-xs font-bold">All</button>
                        <button type="button" @click="userFilter = 'online'" :class="userFilter === 'online' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground'" class="rounded-md px-3 py-1.5 text-xs font-bold">Online</button>
                    </div>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto p-3">
                    <template x-for="user in visibleUsers" :key="user.id">
                        <button type="button" @click="startDirect(user.id)" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-accent">
                            <span class="relative flex size-9 items-center justify-center rounded-lg bg-background text-xs font-black text-foreground">
                                <span x-text="initials(user.name)"></span>
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-card" :class="user.is_online ? 'bg-emerald-500' : 'bg-muted-foreground/40'"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-bold text-foreground" x-text="user.name"></span>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black" :class="user.is_online ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-secondary text-muted-foreground'" x-text="user.is_online ? 'Live' : 'Away'"></span>
                                </span>
                                <span class="block truncate text-xs text-muted-foreground" x-text="user.last_seen_label"></span>
                                <span class="block truncate text-[11px] text-muted-foreground/80" x-text="[user.location, user.active_device].filter(Boolean).join(' - ') || user.email"></span>
                            </span>
                        </button>
                    </template>
                    <div x-show="visibleUsers.length === 0" class="px-3 py-8 text-center text-sm font-semibold text-muted-foreground">
                        No users found.
                    </div>
                </div>
            </aside>
        </div>

        <div x-show="showGroupModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-background/70 p-4 backdrop-blur-sm">
            <form @submit.prevent="createGroup" class="w-full max-w-lg rounded-lg border border-border bg-card p-5 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-black text-foreground">New Group</h2>
                    <button type="button" @click="showGroupModal = false" class="rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-foreground"><x-ui.icon name="x" size="5" /></button>
                </div>
                <div class="space-y-4">
                    <input x-model="groupForm.name" class="h-11 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15" placeholder="Group name" required>
                    <textarea x-model="groupForm.description" class="min-h-24 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/15" placeholder="Description"></textarea>
                    <select x-model="groupForm.privacy" class="h-11 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15">
                        <option value="private">Private</option>
                        <option value="public">Public</option>
                    </select>
                    <div class="max-h-48 overflow-y-auto rounded-lg border border-border p-2">
                        <template x-for="user in users" :key="user.id">
                            <label class="flex items-center gap-3 rounded-md px-2 py-2 text-sm hover:bg-accent">
                                <input type="checkbox" :value="user.id" x-model="groupForm.member_ids" class="rounded border-border">
                                <span class="font-semibold text-foreground" x-text="user.name"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="showGroupModal = false" class="rounded-lg border border-border px-4 py-2 text-sm font-bold">Cancel</button>
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-black text-primary-foreground">Create</button>
                </div>
            </form>
        </div>

        <div x-show="showGroupSettingsModal && activeConversation?.type === 'group'" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-background/70 p-4 backdrop-blur-sm">
            <div class="flex max-h-[92svh] w-full max-w-4xl flex-col overflow-hidden rounded-lg border border-border bg-card shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-black text-foreground" x-text="conversationTitle(activeConversation)"></h2>
                        <p class="truncate text-xs font-semibold text-muted-foreground" x-text="conversationSubtitle(activeConversation)"></p>
                    </div>
                    <button type="button" @click="closeGroupSettings" class="inline-flex size-10 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-accent hover:text-foreground">
                        <x-ui.icon name="x" size="5" />
                    </button>
                </div>

                <div x-show="errorMessage" x-cloak class="mx-5 mt-4 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs font-semibold text-destructive" x-text="errorMessage"></div>

                <div class="grid min-h-0 flex-1 grid-cols-1 gap-0 overflow-y-auto lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.85fr)]">
                    <div class="space-y-5 border-b border-border p-5 lg:border-b-0 lg:border-r">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-black uppercase tracking-widest text-muted-foreground">Group Details</h3>
                                <button type="button" x-show="canManageSettings" @click="updateGroup" :disabled="groupBusy" class="inline-flex h-9 items-center gap-2 rounded-lg bg-primary px-3 text-xs font-black text-primary-foreground transition hover:opacity-90 disabled:opacity-50">
                                    <x-ui.icon name="save" size="4" />
                                    Save
                                </button>
                            </div>

                            <template x-if="canManageSettings">
                                <div class="space-y-3">
                                    <input x-model="groupSettingsForm.name" class="h-11 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15" placeholder="Group name">
                                    <textarea x-model="groupSettingsForm.description" rows="4" class="min-h-28 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/15" placeholder="Description"></textarea>
                                    <select x-model="groupSettingsForm.privacy" class="h-11 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15">
                                        <option value="private">Private</option>
                                        <option value="public">Public</option>
                                    </select>
                                </div>
                            </template>

                            <template x-if="!canManageSettings">
                                <div class="rounded-lg border border-border bg-background p-4">
                                    <p class="text-sm font-bold text-foreground" x-text="activeConversation?.name || 'Group'"></p>
                                    <p class="mt-1 whitespace-pre-wrap text-sm text-muted-foreground" x-text="activeConversation?.description || 'No description added.'"></p>
                                    <p class="mt-3 text-xs font-black uppercase tracking-widest text-muted-foreground" x-text="activeConversation?.privacy || 'private'"></p>
                                </div>
                            </template>
                        </div>

                        <div x-show="canManageMembers" class="space-y-3">
                            <h3 class="text-sm font-black uppercase tracking-widest text-muted-foreground">Add Member</h3>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_9rem_2.75rem]">
                                <select x-model="groupMemberForm.user_id" class="h-11 min-w-0 rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15">
                                    <option value="">Select user</option>
                                    <template x-for="user in availableGroupUsers" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                                <select x-model="groupMemberForm.role" x-show="canManageSettings" class="h-11 rounded-lg border border-border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/15">
                                    <option value="member">Member</option>
                                    <option value="moderator">Moderator</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <button type="button" @click="addGroupMember" :disabled="groupBusy || !groupMemberForm.user_id" class="inline-flex h-11 items-center justify-center rounded-lg bg-primary text-primary-foreground transition hover:opacity-90 disabled:opacity-50">
                                    <x-ui.icon name="user-plus" size="4" />
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-border pt-5">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" x-show="!isOwner" @click="leaveGroup" :disabled="groupBusy" class="rounded-lg border border-border px-4 py-2 text-sm font-black text-muted-foreground transition hover:bg-accent disabled:opacity-50">Leave Group</button>
                                <button type="button" x-show="isOwner" @click="deleteGroup" :disabled="groupBusy" class="rounded-lg border border-destructive/30 px-4 py-2 text-sm font-black text-destructive transition hover:bg-destructive/10 disabled:opacity-50">Delete Group</button>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-black uppercase tracking-widest text-muted-foreground">Members</h3>
                            <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-black text-secondary-foreground" x-text="`${activeConversation?.active_members?.length || 0}`"></span>
                        </div>

                        <div class="max-h-[50svh] space-y-2 overflow-y-auto pr-1 lg:max-h-[60svh]">
                            <template x-for="member in activeConversation.active_members || []" :key="member.id">
                                <div class="rounded-lg border border-border bg-background px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 items-center justify-center rounded-md bg-secondary text-[11px] font-black text-secondary-foreground" x-text="initials(member.user?.name)"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-foreground" x-text="member.user?.name || 'User'"></p>
                                            <p class="text-[11px] font-semibold capitalize text-muted-foreground" x-text="member.role"></p>
                                        </div>
                                        <button type="button" x-show="canManageMembers && member.role !== 'owner' && member.user_id !== currentUserId" @click="removeGroupMember(member)" :disabled="groupBusy" class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition hover:bg-destructive/10 hover:text-destructive disabled:opacity-50" title="Remove member">
                                            <x-ui.icon name="user-minus" size="4" />
                                        </button>
                                    </div>

                                    <div x-show="canManageSettings && member.role !== 'owner'" class="mt-3 grid grid-cols-[minmax(0,1fr)_auto] gap-2">
                                        <select :value="member.role" @change="updateMemberRole(member, $event.target.value)" class="h-9 min-w-0 rounded-md border border-border bg-card px-2 text-xs font-semibold capitalize outline-none focus:ring-2 focus:ring-primary/15">
                                            <option value="member">Member</option>
                                            <option value="moderator">Moderator</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <button type="button" x-show="isOwner && member.user_id !== currentUserId" @click="transferOwner(member)" :disabled="groupBusy" class="rounded-md border border-border px-3 text-[10px] font-black uppercase tracking-wider text-muted-foreground transition hover:bg-accent disabled:opacity-50">Make Owner</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatWorkspace(initial) {
            return {
                currentUserId: @js(auth()->id()),
                conversations: initial.conversations || [],
                users: initial.users || [],
                pollInterval: initial.pollInterval || 5000,
                activeConversation: null,
                messages: [],
                presence: [],
                search: '',
                userSearch: '',
                filter: 'all',
                userFilter: 'all',
                draft: '',
                replyTo: null,
                editingMessageId: null,
                editingDraft: '',
                busyMessageId: null,
                sendingMessage: false,
                pendingAttachments: [],
                errorMessage: '',
                showGroupModal: false,
                showGroupSettingsModal: false,
                groupForm: { name: '', description: '', privacy: 'private', member_ids: [] },
                groupSettingsForm: { name: '', description: '', privacy: 'private' },
                groupMemberForm: { user_id: '', role: 'member' },
                groupBusy: false,
                lastTypingSentAt: 0,
                searchResults: { users: [], groups: [], messages: [] },
                poller: null,

                get onlineLabel() {
                    const count = this.onlineUsers.length;
                    return count + ' online now';
                },

                get onlineUsers() {
                    return this.users.filter((user) => user.is_online);
                },

                get visibleUsers() {
                    if (this.userFilter === 'online') {
                        return this.onlineUsers;
                    }

                    return this.users;
                },

                get filteredConversations() {
                    return this.conversations.filter((conversation) => {
                        if (this.filter === 'unread') return conversation.unread_count > 0;
                        if (this.filter === 'pinned') return conversation.active_members?.some((member) => member.user_id === this.currentUserId && member.pinned_at);
                        return true;
                    });
                },

                get activeMember() {
                    return this.activeConversation?.active_members?.find((member) => member.user_id === this.currentUserId) || null;
                },

                get canManageMembers() {
                    return ['owner', 'admin', 'moderator'].includes(this.activeMember?.role);
                },

                get canManageSettings() {
                    return ['owner', 'admin'].includes(this.activeMember?.role);
                },

                get isOwner() {
                    return this.activeMember?.role === 'owner';
                },

                get availableGroupUsers() {
                    const memberIds = new Set((this.activeConversation?.active_members || []).map((member) => Number(member.user_id)));
                    return this.users.filter((user) => !memberIds.has(Number(user.id)));
                },

                init() {
                    this.refresh();
                    this.updatePresence('online');
                    this.poller = setInterval(() => this.refresh(), this.pollInterval);
                    window.addEventListener('beforeunload', () => this.updatePresence('offline'));
                },

                async refresh() {
                    try {
                        const [conversations, presence, users] = await Promise.all([
                            axios.get('/chat/api/conversations'),
                            axios.get('/chat/api/presence'),
                            axios.get('/chat/api/users', { params: { q: this.userSearch || undefined } }),
                        ]);
                        this.conversations = conversations.data.data;
                        this.presence = presence.data.data;
                        this.users = users.data.data;
                        if (this.activeConversation) {
                            const updated = this.conversations.find((item) => item.id === this.activeConversation.id);
                            if (updated) this.activeConversation = updated;
                            await this.loadMessages();
                        }
                    } catch (error) {
                        this.showError(error, 'Unable to refresh chat.');
                    }
                },

                async fetchUsers() {
                    const response = await axios.get('/chat/api/users', { params: { q: this.userSearch || undefined } });
                    this.users = response.data.data;
                },

                async runSearch() {
                    if (!this.search.trim()) {
                        this.searchResults = { users: [], groups: [], messages: [] };
                        return;
                    }
                    const response = await axios.get('/chat/api/search', { params: { q: this.search } });
                    this.searchResults = response.data.data;
                },

                async selectConversation(conversation) {
                    this.activeConversation = conversation;
                    this.replyTo = null;
                    this.cancelEdit();
                    this.syncGroupSettingsForm();
                    await this.loadMessages();
                },

                async loadMessages() {
                    if (!this.activeConversation) return;
                    try {
                        const response = await axios.get(`/chat/api/conversations/${this.activeConversation.id}/messages`);
                        this.messages = [...response.data.data.data].reverse();
                        const last = this.messages[this.messages.length - 1];
                        if (last) await axios.post(`/chat/api/conversations/${this.activeConversation.id}/read`, { message_id: last.id });
                        this.$nextTick(() => {
                            if (this.$refs.messageScroller) this.$refs.messageScroller.scrollTop = this.$refs.messageScroller.scrollHeight;
                        });
                    } catch (error) {
                        this.showError(error, 'Unable to load messages.');
                    }
                },

                async sendMessage() {
                    const content = this.draft.trim();
                    if ((!content && this.pendingAttachments.length === 0) || !this.activeConversation || this.sendingMessage) return;
                    this.errorMessage = '';
                    this.sendingMessage = true;
                    try {
                        const formData = new FormData();
                        formData.append('type', this.pendingAttachments.length ? this.messageTypeForFiles(this.pendingAttachments) : 'text');
                        if (content) formData.append('content', content);
                        if (this.replyTo?.id) formData.append('parent_id', this.replyTo.id);
                        this.pendingAttachments.forEach((file) => formData.append('files[]', file));

                        await axios.post(`/chat/api/conversations/${this.activeConversation.id}/messages`, formData, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });
                        this.draft = '';
                        this.pendingAttachments = [];
                        if (this.$refs.attachmentInput) this.$refs.attachmentInput.value = '';
                        this.replyTo = null;
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to send message.');
                    } finally {
                        this.sendingMessage = false;
                    }
                },

                handleAttachmentSelection(event) {
                    const files = Array.from(event.target.files || []);
                    const maxBytes = @js(config('chat.uploads.max_size_kb') * 1024);
                    const accepted = [];

                    for (const file of files) {
                        if (file.size > maxBytes) {
                            this.errorMessage = `${file.name} is larger than ${this.formatFileSize(maxBytes)}.`;
                            continue;
                        }

                        accepted.push(file);
                    }

                    this.pendingAttachments = [...this.pendingAttachments, ...accepted].slice(0, 10);
                    event.target.value = '';
                },

                removePendingAttachment(index) {
                    this.pendingAttachments.splice(index, 1);
                },

                messageTypeForFiles(files) {
                    return files.every((file) => file.type?.startsWith('image/')) ? 'image' : 'file';
                },

                async startDirect(userId) {
                    const response = await axios.post('/chat/api/conversations', { type: 'direct', user_id: userId });
                    await this.refresh();
                    this.selectConversation(response.data.data);
                },

                async createGroup() {
                    const response = await axios.post('/chat/api/conversations', { type: 'group', ...this.groupForm });
                    this.showGroupModal = false;
                    this.groupForm = { name: '', description: '', privacy: 'private', member_ids: [] };
                    await this.refresh();
                    this.selectConversation(response.data.data);
                },

                openGroupSettings() {
                    if (this.activeConversation?.type !== 'group') return;
                    this.errorMessage = '';
                    this.syncGroupSettingsForm();
                    this.showGroupSettingsModal = true;
                },

                closeGroupSettings() {
                    this.showGroupSettingsModal = false;
                    this.groupMemberForm = { user_id: '', role: 'member' };
                },

                syncGroupSettingsForm() {
                    if (this.activeConversation?.type !== 'group') return;
                    this.groupSettingsForm = {
                        name: this.activeConversation.name || '',
                        description: this.activeConversation.description || '',
                        privacy: this.activeConversation.privacy || 'private',
                    };
                    this.groupMemberForm = { user_id: '', role: 'member' };
                },

                replaceActiveConversation(conversation) {
                    if (!conversation) return;
                    this.activeConversation = conversation;
                    this.conversations = this.conversations.map((item) => item.id === conversation.id ? conversation : item);
                    this.syncGroupSettingsForm();
                },

                async updateGroup() {
                    if (!this.activeConversation || !this.canManageSettings) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        const response = await axios.put(`/chat/api/groups/${this.activeConversation.id}`, this.groupSettingsForm);
                        this.replaceActiveConversation(response.data.data);
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to update group.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async addGroupMember() {
                    if (!this.activeConversation || !this.groupMemberForm.user_id || !this.canManageMembers) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        const response = await axios.post(`/chat/api/groups/${this.activeConversation.id}/members`, this.groupMemberForm);
                        this.groupMemberForm = { user_id: '', role: 'member' };
                        this.replaceActiveConversation(response.data.conversation);
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to add member.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async removeGroupMember(member) {
                    if (!this.activeConversation || !window.confirm(`Remove ${member.user?.name || 'this member'} from the group?`)) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        await axios.post(`/chat/api/groups/${this.activeConversation.id}/members/remove`, { user_id: member.user_id });
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to remove member.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async updateMemberRole(member, role) {
                    if (!this.activeConversation || member.role === role) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        const response = await axios.post(`/chat/api/groups/${this.activeConversation.id}/members/role`, { user_id: member.user_id, role });
                        this.replaceActiveConversation(response.data.conversation);
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to update role.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async transferOwner(member) {
                    if (!this.activeConversation || !window.confirm(`Transfer ownership to ${member.user?.name || 'this member'}?`)) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        const response = await axios.post(`/chat/api/groups/${this.activeConversation.id}/transfer-owner`, { user_id: member.user_id });
                        this.replaceActiveConversation(response.data.conversation);
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to transfer ownership.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async leaveGroup() {
                    if (!this.activeConversation || !window.confirm('Leave this group?')) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        await axios.post(`/chat/api/groups/${this.activeConversation.id}/leave`);
                        this.activeConversation = null;
                        this.messages = [];
                        this.showGroupSettingsModal = false;
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to leave group.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                async deleteGroup() {
                    if (!this.activeConversation || !window.confirm('Delete this group for everyone?')) return;
                    this.groupBusy = true;
                    this.errorMessage = '';
                    try {
                        await axios.delete(`/chat/api/groups/${this.activeConversation.id}`);
                        this.activeConversation = null;
                        this.messages = [];
                        this.showGroupSettingsModal = false;
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to delete group.');
                    } finally {
                        this.groupBusy = false;
                    }
                },

                setReply(message) {
                    this.replyTo = message;
                    this.cancelEdit();
                    this.$nextTick(() => this.$refs.composer?.focus());
                },

                startEdit(message) {
                    this.errorMessage = '';
                    this.replyTo = null;
                    this.editingMessageId = message.id;
                    this.editingDraft = message.content || '';
                },

                cancelEdit() {
                    this.editingMessageId = null;
                    this.editingDraft = '';
                },

                async saveEdit(message) {
                    const content = this.editingDraft.trim();
                    if (!content) {
                        this.errorMessage = 'Edited message cannot be empty.';
                        return;
                    }

                    this.busyMessageId = message.id;
                    this.errorMessage = '';

                    try {
                        const response = await axios.post(`/chat/api/messages/${message.id}/edit`, { content });
                        this.messages = this.messages.map((item) => item.id === message.id ? response.data.data : item);
                        this.cancelEdit();
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to edit message.');
                    } finally {
                        this.busyMessageId = null;
                    }
                },

                async deleteMessage(message) {
                    if (!window.confirm('Delete this message?')) return;
                    this.busyMessageId = message.id;
                    this.errorMessage = '';

                    try {
                        await axios.post(`/chat/api/messages/${message.id}/delete`);
                        this.messages = this.messages.filter((item) => item.id !== message.id);
                        if (this.replyTo?.id === message.id) this.replyTo = null;
                        await this.refresh();
                    } catch (error) {
                        this.showError(error, 'Unable to delete message.');
                    } finally {
                        this.busyMessageId = null;
                    }
                },

                scrollToMessage(messageId) {
                    const target = this.messages.find((message) => message.id === messageId);
                    if (!target) return;
                    this.$nextTick(() => {
                        const index = this.messages.findIndex((message) => message.id === messageId);
                        const nodes = this.$refs.messageScroller?.children || [];
                        nodes[index]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                },

                showError(error, fallback) {
                    const validation = error?.response?.data?.errors;
                    const firstValidation = validation ? Object.values(validation).flat()[0] : null;
                    this.errorMessage = firstValidation || error?.response?.data?.message || fallback;
                },

                async toggleArchive() {
                    if (!this.activeConversation) return;
                    await axios.post(`/chat/api/conversations/${this.activeConversation.id}/archive`, { archived: true });
                    await this.refresh();
                },

                async togglePin() {
                    if (!this.activeConversation) return;
                    await axios.post(`/chat/api/conversations/${this.activeConversation.id}/pin`, { pinned: true });
                    await this.refresh();
                },

                async sendTyping() {
                    if (!this.activeConversation) return;
                    const now = Date.now();
                    if (now - this.lastTypingSentAt < 3000) return;
                    this.lastTypingSentAt = now;
                    await axios.post('/chat/api/presence', { status: 'online', typing_conversation_id: this.activeConversation.id });
                },

                async updatePresence(status) {
                    try {
                        await axios.post('/chat/api/presence', { status });
                    } catch (error) {}
                },

                conversationTitle(conversation) {
                    if (conversation.type === 'group') return conversation.name || 'Group';
                    const member = conversation.active_members?.find((item) => item.user_id !== this.currentUserId);
                    return member?.user?.name || 'Private chat';
                },

                conversationSubtitle(conversation) {
                    if (conversation.type === 'group') return `${conversation.active_members?.length || 0} members`;
                    const member = conversation.active_members?.find((item) => item.user_id !== this.currentUserId);
                    return member?.user?.email || 'Direct message';
                },

                conversationInitials(conversation) {
                    return this.initials(this.conversationTitle(conversation));
                },

                initials(name) {
                    return (name || 'U').split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
                },

                lastPreview(conversation) {
                    return conversation.messages?.[0]?.content || (conversation.type === 'group' ? 'Group created' : 'Direct chat');
                },

                lastTime(item) {
                    const value = item.created_at || item.updated_at;
                    return value ? new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                },

                isOnline(conversation) {
                    if (conversation.type !== 'direct') return false;
                    const member = conversation.active_members?.find((item) => item.user_id !== this.currentUserId);
                    return this.presence.some((item) => item.user_id === member?.user_id && item.status === 'online');
                },
            };
        }
    </script>
</x-layouts.app>
