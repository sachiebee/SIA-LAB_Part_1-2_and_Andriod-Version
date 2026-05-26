package com.example.memeverseapp.models;

import java.util.List;

public class Post {
    private int id;
    private int user_id;
    private String title;
    private String description;
    private String tags;
    private String image_path;
    private String category_name;
    private String category_slug;
    private int vote_score;
    private int comment_count;
    private int user_vote;
    private String username;
    private String nickname;
    private String avatar_url;
    private String created_at;
    private int category_id;
    private List<Comment> comments;

    // Getters and Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public int getUser_id() { return user_id; }
    public void setUser_id(int user_id) { this.user_id = user_id; }
    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }
    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }
    public String getTags() { return tags; }
    public void setTags(String tags) { this.tags = tags; }
    public String getImage_path() { return image_path; }
    public void setImage_path(String image_path) { this.image_path = image_path; }
    public String getCategory_name() { return category_name; }
    public void setCategory_name(String category_name) { this.category_name = category_name; }
    public String getCategory_slug() { return category_slug; }
    public void setCategory_slug(String category_slug) { this.category_slug = category_slug; }
    public int getVote_score() { return vote_score; }
    public void setVote_score(int vote_score) { this.vote_score = vote_score; }
    public int getComment_count() { return comment_count; }
    public void setComment_count(int comment_count) { this.comment_count = comment_count; }
    public int getUser_vote() { return user_vote; }
    public void setUser_vote(int user_vote) { this.user_vote = user_vote; }
    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }
    public String getNickname() { return nickname; }
    public void setNickname(String nickname) { this.nickname = nickname; }
    public String getAvatar_url() { return avatar_url; }
    public void setAvatar_url(String avatar_url) { this.avatar_url = avatar_url; }
    public String getCreated_at() { return created_at; }
    public void setCreated_at(String created_at) { this.created_at = created_at; }
    public int getCategory_id() { return category_id; }
    public void setCategory_id(int category_id) { this.category_id = category_id; }
    public List<Comment> getComments() { return comments; }
    public void setComments(List<Comment> comments) { this.comments = comments; }
}