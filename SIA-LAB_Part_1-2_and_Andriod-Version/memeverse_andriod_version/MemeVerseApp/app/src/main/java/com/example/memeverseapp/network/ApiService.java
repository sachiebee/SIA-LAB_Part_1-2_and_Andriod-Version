package com.example.memeverseapp.network;

import com.example.memeverseapp.models.*;
import okhttp3.MultipartBody;
import retrofit2.Call;
import retrofit2.http.*;
import java.util.List;

public interface ApiService {
    // ========== AUTH ==========
    @FormUrlEncoded
    @POST("api/login.php")
    Call<LoginResponse> login(@Field("login") String login, @Field("password") String password);

    @FormUrlEncoded
    @POST("api/register.php")
    Call<ApiResponse> register(@Field("username") String username,
                               @Field("email") String email,
                               @Field("password") String password,
                               @Field("confirm_password") String confirm);

    // ========== POSTS ==========
    @GET("api/posts.php")
    Call<List<Post>> getPosts(@Query("page") int page, @Query("limit") int limit);

    @GET("api/get_post.php")
    Call<PostDetailResponse> getPost(@Query("id") int postId);

    @GET("api/user_posts.php")
    Call<UserPostsResponse> getUserPosts(@Query("user_id") int userId);

    // EDIT POST - ADD THIS METHOD
    @FormUrlEncoded
    @POST("api/edit_post.php")
    Call<ApiResponse> editPost(@Field("post_id") int postId,
                               @Field("title") String title,
                               @Field("description") String description,
                               @Field("category_id") int categoryId);

    // DELETE POST
    @FormUrlEncoded
    @POST("api/delete_post.php")
    Call<ApiResponse> deletePost(@Field("post_id") int postId);

    // VOTE
    @FormUrlEncoded
    @POST("api/vote.php")
    Call<VoteResponse> vote(@Field("post_id") int postId, @Field("vote") String vote);

    // ========== COMMENTS ==========
    @FormUrlEncoded
    @POST("api/comment.php")
    Call<ApiResponse> addComment(@Field("post_id") int postId,
                                 @Field("comment") String comment,
                                 @Field("parent_id") int parentId);

    @HTTP(method = "DELETE", path = "api/comment.php", hasBody = true)
    Call<ApiResponse> deleteComment(@Body CommentDeleteBody body);

    // ========== MESSAGES ==========
    @GET("api/get_conversations.php")
    Call<ConversationsResponse> getConversations();

    @GET("api/get_messages.php")
    Call<MessagesResponse> getMessages(@Query("with") int withUserId);

    @POST("api/send_message.php")
    Call<ApiResponse> sendMessage(@Body SendMessageBody body);

    @FormUrlEncoded
    @POST("api/delete_conversation.php")
    Call<ApiResponse> deleteConversation(@Field("user_id") int userId);

    // ========== NOTIFICATIONS ==========
    @GET("api/latest_notifications.php")
    Call<NotificationsResponse> getLatestNotifications();

    @POST("api/mark_all_notifications_read.php")
    Call<ApiResponse> markAllNotificationsRead();

    @GET("api/unread_notifications.php")
    Call<UnreadCountResponse> getUnreadNotifications();

    @GET("api/unread_messages.php")
    Call<UnreadCountResponse> getUnreadMessages();

    // ========== PROFILE ==========
    @GET("api/get_user.php")
    Call<UserResponse> getUser(@Query("id") int userId);

    @Multipart
    @POST("api/update_profile.php")
    Call<ApiResponse> updateProfile(@Part("nickname") String nickname,
                                    @Part("bio") String bio,
                                    @Part MultipartBody.Part avatar);

    // ========== FOLLOW / REPORT ==========
    @FormUrlEncoded
    @POST("api/follow.php")
    Call<ApiResponse> follow(@Field("user_id") int userId, @Field("action") String action);

    @POST("api/report.php")
    Call<ApiResponse> report(@Body ReportBody body);

    // ========== SEARCH ==========
    @GET("api/search.php")
    Call<SearchResponse> search(@Query("q") String query);

    // ========== UPLOAD ==========
    @Multipart
    @POST("api/upload.php")
    Call<ApiResponse> uploadPost(@Part("title") String title,
                                 @Part("description") String description,
                                 @Part("category_id") int categoryId,
                                 @Part MultipartBody.Part image);
}