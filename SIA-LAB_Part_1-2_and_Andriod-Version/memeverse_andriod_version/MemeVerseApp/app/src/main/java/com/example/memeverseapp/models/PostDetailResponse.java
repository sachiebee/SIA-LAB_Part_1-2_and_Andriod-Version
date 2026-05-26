package com.example.memeverseapp.models;

public class PostDetailResponse {
    private boolean success;
    private Post post;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public Post getPost() { return post; }
    public void setPost(Post post) { this.post = post; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}