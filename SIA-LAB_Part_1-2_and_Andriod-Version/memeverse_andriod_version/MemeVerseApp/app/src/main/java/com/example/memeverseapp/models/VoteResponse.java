package com.example.memeverseapp.models;

public class VoteResponse {
    private boolean success;
    private int new_score;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public int getNew_score() { return new_score; }
    public void setNew_score(int new_score) { this.new_score = new_score; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}