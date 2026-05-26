package com.example.memeverseapp.models;

import java.util.List;

public class ConversationsResponse {
    private boolean success;
    private List<Conversation> conversations;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public List<Conversation> getConversations() { return conversations; }
    public void setConversations(List<Conversation> conversations) { this.conversations = conversations; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}