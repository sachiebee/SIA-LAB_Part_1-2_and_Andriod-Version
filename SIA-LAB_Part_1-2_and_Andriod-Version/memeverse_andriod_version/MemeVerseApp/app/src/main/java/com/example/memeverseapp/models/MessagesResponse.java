package com.example.memeverseapp.models;

import java.util.List;

public class MessagesResponse {
    private boolean success;
    private List<Message> messages;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public List<Message> getMessages() { return messages; }
    public void setMessages(List<Message> messages) { this.messages = messages; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}