import { useState, useCallback } from 'react';
import { ConversationMessage, ChatResponse } from '@/api/server/pterogpt';

interface UseConversationReturn {
    messages: ConversationMessage[];
    addUserMessage: (content: string) => void;
    addAssistantMessage: (response: ChatResponse) => void;
    updateLastMessage: (content: string) => void;
    clearConversation: () => void;
    getHistory: () => ConversationMessage[];
}

export const useConversation = (): UseConversationReturn => {
    const [messages, setMessages] = useState<ConversationMessage[]>([]);

    const addUserMessage = useCallback((content: string) => {
        setMessages((prev: ConversationMessage[]) => [...prev, { role: 'user', content }]);
    }, []);

    const addAssistantMessage = useCallback((response: ChatResponse) => {
        setMessages((prev: ConversationMessage[]) => [...prev, { role: 'assistant', content: response.response }]);
    }, []);

    const updateLastMessage = useCallback((content: string) => {
        setMessages((prev: ConversationMessage[]) => {
            const last = prev[prev.length - 1];
            if (last && last.role === 'assistant') {
                return [...prev.slice(0, -1), { ...last, content: last.content + content }];
            }
            return [...prev, { role: 'assistant', content }];
        });
    }, []);

    const clearConversation = useCallback(() => {
        setMessages([]);
    }, []);

    const getHistory = useCallback(() => {
        return messages.slice(-20);
    }, [messages]);

    return {
        messages,
        addUserMessage,
        addAssistantMessage,
        updateLastMessage,
        clearConversation,
        getHistory,
    };
};