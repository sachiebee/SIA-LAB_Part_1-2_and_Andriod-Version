package com.example.memeverseapp.models;

public class UserResponse {
    private boolean success;
    private User user;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public User getUser() { return user; }
    public void setUser(User user) { this.user = user; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}