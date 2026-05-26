package com.example.memeverseapp.models;

import java.util.List;

public class UserPostsResponse {
    private boolean success;
    private List<Post> posts;
    private String error;

    public boolean isSuccess() { return success; }
    public void setSuccess(boolean success) { this.success = success; }
    public List<Post> getPosts() { return posts; }
    public void setPosts(List<Post> posts) { this.posts = posts; }
    public String getError() { return error; }
    public void setError(String error) { this.error = error; }
}