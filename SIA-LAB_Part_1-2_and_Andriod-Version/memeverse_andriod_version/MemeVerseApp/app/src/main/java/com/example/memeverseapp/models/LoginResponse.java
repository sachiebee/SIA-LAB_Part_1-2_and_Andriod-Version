package com.example.memeverseapp.models;

public class LoginResponse {
    private boolean success;
    private int user_id;
    private String username;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public int getUser_id() { return user_id; }
    public void setUser_id(int user_id) { this.user_id = user_id; }
    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}