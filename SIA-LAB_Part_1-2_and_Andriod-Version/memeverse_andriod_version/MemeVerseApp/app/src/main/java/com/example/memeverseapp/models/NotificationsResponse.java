package com.example.memeverseapp.models;

import java.util.List;

public class NotificationsResponse {
    private List<Notification> notifications;
    private int unread_count;

    public List<Notification> getNotifications() { return notifications; }
    public void setNotifications(List<Notification> notifications) { this.notifications = notifications; }
    public int getUnread_count() { return unread_count; }
    public void setUnread_count(int unread_count) { this.unread_count = unread_count; }
}