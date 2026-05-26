package com.example.memeverseapp.models;

import java.util.List;

public class SearchResponse {
    private List<Post> posts;
    private List<User> users;
    private List<Category> categories;

    public List<Post> getPosts() { return posts; }
    public void setPosts(List<Post> posts) { this.posts = posts; }
    public List<User> getUsers() { return users; }
    public void setUsers(List<User> users) { this.users = users; }
    public List<Category> getCategories() { return categories; }
    public void setCategories(List<Category> categories) { this.categories = categories; }
}