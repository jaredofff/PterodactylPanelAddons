import http from '@/api/http';

export interface PteroGPTConfig {
    enabled: boolean;
    ui_mode: 'panel' | 'modal';
    model_mode: 'fixed' | 'list';
    model?: string;
    models?: string[];
}

export interface RateLimits {
    chat: { used: number; limit: number; remaining: number };
    read: { used: number; limit: number; remaining: number };
    write: { used: number; limit: number; remaining: number };
    command: { used: number; limit: number; remaining: number };
}

export interface ChatContext {
    console_lines?: string;
    file_path?: string;
    file_content?: string;
}

export interface ConversationMessage {
    role: 'user' | 'assistant';
    content: string;
}

export interface ChatResponse {
    response: string;
}

export const getConfig = async (uuid: string): Promise<PteroGPTConfig> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/pterogpt/config`);
    return data.data;
};

export const getLimits = async (uuid: string): Promise<RateLimits> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/pterogpt/limits`);
    return data.data;
};

export const sendChat = async (
    uuid: string,
    message: string,
    context?: ChatContext,
    conversationHistory?: ConversationMessage[],
    model?: string
): Promise<ChatResponse> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/pterogpt/chat`, {
        message,
        context,
        conversation_history: conversationHistory,
        model,
    });
    return data.data;
};

export const sendChatStream = async (
    uuid: string,
    message: string,
    onContent: (content: string) => void,
    context?: ChatContext,
    conversationHistory?: ConversationMessage[],
    model?: string
): Promise<void> => {
    const response = await fetch(`/api/client/servers/${uuid}/pterogpt/chat`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'text/event-stream',
            'X-Requested-With': 'XMLHttpRequest',
            // Pterodactyl uses a CSRF token in a meta tag or cookie
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
        body: JSON.stringify({
            message,
            context,
            conversation_history: conversationHistory,
            model,
            stream: true,
        }),
    });

    if (!response.ok) {
        throw new Error('Failed to connect to AI stream');
    }

    const reader = response.body?.getReader();
    const decoder = new TextDecoder();

    if (!reader) return;

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value);
        const lines = chunk.split('\n');

        for (const line of lines) {
            if (line.startsWith('data: ')) {
                try {
                    const data = JSON.parse(line.substring(6));
                    if (data.content) {
                        onContent(data.content);
                    }
                } catch (e) {
                    // Ignore malformed JSON
                }
            }
        }
    }
};