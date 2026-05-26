package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import com.example.memeverseapp.R;
import com.example.memeverseapp.adapters.PostAdapter;
import com.example.memeverseapp.models.Post;
import com.example.memeverseapp.models.VoteResponse;
import com.example.memeverseapp.network.ApiService;
import com.example.memeverseapp.network.RetrofitClient;
import com.example.memeverseapp.ui.PostDetailFragment;
import com.example.memeverseapp.utils.PreferencesManager;
import com.example.memeverseapp.utils.ToastUtils;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import java.util.ArrayList;
import java.util.List;

public class HomeFragment extends Fragment implements
        PostAdapter.OnVoteClickListener,
        PostAdapter.OnCommentClickListener,
        PostAdapter.OnProfileClickListener {

    private static final String TAG = "HomeFragment";
    private RecyclerView recyclerView;
    private SwipeRefreshLayout swipeRefresh;
    private ProgressBar progressBar;
    private TextView tvEmpty;
    private PostAdapter adapter;
    private List<Post> posts = new ArrayList<>();
    private ApiService apiService;
    private PreferencesManager prefManager;
    private int currentPage = 1;
    private boolean isLoading = false;
    private boolean hasMore = true;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_home, container, false);

        recyclerView = view.findViewById(R.id.recyclerView);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        progressBar = view.findViewById(R.id.progressBar);
        tvEmpty = view.findViewById(R.id.tvEmpty);

        apiService = RetrofitClient.getClient().create(ApiService.class);
        prefManager = new PreferencesManager(requireContext());

        adapter = new PostAdapter(posts, this, this, this);
        recyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
        recyclerView.setAdapter(adapter);

        swipeRefresh.setOnRefreshListener(this::refreshPosts);

        recyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                LinearLayoutManager layoutManager = (LinearLayoutManager) recyclerView.getLayoutManager();
                int totalItemCount = layoutManager.getItemCount();
                int lastVisible = layoutManager.findLastVisibleItemPosition();
                if (!isLoading && hasMore && lastVisible >= totalItemCount - 3) {
                    loadMorePosts();
                }
            }
        });

        loadPosts();
        return view;
    }

    private void loadPosts() {
        isLoading = true;
        progressBar.setVisibility(View.VISIBLE);
        tvEmpty.setVisibility(View.GONE);

        Log.d(TAG, "Loading posts from: " + RetrofitClient.getClient().baseUrl());

        apiService.getPosts(currentPage, 10).enqueue(new Callback<List<Post>>() {
            @Override
            public void onResponse(Call<List<Post>> call, Response<List<Post>> response) {
                isLoading = false;
                progressBar.setVisibility(View.GONE);
                swipeRefresh.setRefreshing(false);

                Log.d(TAG, "Response code: " + response.code());
                Log.d(TAG, "Response successful: " + response.isSuccessful());

                if (response.isSuccessful() && response.body() != null) {
                    List<Post> newPosts = response.body();
                    Log.d(TAG, "Posts received: " + newPosts.size());

                    if (newPosts.isEmpty()) {
                        hasMore = false;
                        if (posts.isEmpty()) {
                            tvEmpty.setVisibility(View.VISIBLE);
                            tvEmpty.setText("No memes yet!\n\nUpload your first meme using the + button.");
                            recyclerView.setVisibility(View.GONE);
                        }
                    } else {
                        tvEmpty.setVisibility(View.GONE);
                        recyclerView.setVisibility(View.VISIBLE);
                        posts.addAll(newPosts);
                        adapter.notifyDataSetChanged();
                        currentPage++;
                        Log.d(TAG, "Total posts now: " + posts.size());
                        ToastUtils.showSuccess(requireActivity(), "Loaded " + newPosts.size() + " memes!");
                    }
                } else {
                    Log.e(TAG, "Response error: " + response.code());
                    tvEmpty.setVisibility(View.VISIBLE);
                    tvEmpty.setText("Error loading memes.\nCheck your connection.\n\nServer: " + RetrofitClient.getClient().baseUrl());
                    ToastUtils.showError(requireActivity(), "Failed to load posts: " + response.code());
                }
            }

            @Override
            public void onFailure(Call<List<Post>> call, Throwable t) {
                isLoading = false;
                progressBar.setVisibility(View.GONE);
                swipeRefresh.setRefreshing(false);
                Log.e(TAG, "Network error: ", t);
                tvEmpty.setVisibility(View.VISIBLE);
                tvEmpty.setText("Network error!\n\n" + t.getMessage() + "\n\nBase URL: " + RetrofitClient.getClient().baseUrl());
                ToastUtils.showError(requireActivity(), "Network error: " + t.getMessage());
            }
        });
    }

    private void loadMorePosts() {
        loadPosts();
    }

    private void refreshPosts() {
        currentPage = 1;
        posts.clear();
        hasMore = true;
        adapter.notifyDataSetChanged();
        loadPosts();
    }

    @Override
    public void onVote(int postId, String voteType) {
        apiService.vote(postId, voteType).enqueue(new Callback<VoteResponse>() {
            @Override
            public void onResponse(Call<VoteResponse> call, Response<VoteResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    for (Post post : posts) {
                        if (post.getId() == postId) {
                            post.setVote_score(response.body().getNew_score());
                            post.setUser_vote(voteType.equals("up") ? 1 : -1);
                            break;
                        }
                    }
                    adapter.notifyDataSetChanged();
                    ToastUtils.showSuccess(requireActivity(), "Vote recorded!");
                } else {
                    ToastUtils.showError(requireActivity(), "Vote failed");
                }
            }
            @Override
            public void onFailure(Call<VoteResponse> call, Throwable t) {
                ToastUtils.showError(requireActivity(), "Network error: " + t.getMessage());
            }
        });
    }

    @Override
    public void onCommentClick(int postId) {
        PostDetailFragment fragment = new PostDetailFragment();
        Bundle args = new Bundle();
        args.putInt("post_id", postId);
        fragment.setArguments(args);
        requireActivity().getSupportFragmentManager()
                .beginTransaction()
                .replace(R.id.fragment_container, fragment)
                .addToBackStack(null)
                .commit();
    }

    @Override
    public void onProfileClick(int userId) {
        ProfileFragment fragment = new ProfileFragment();
        Bundle args = new Bundle();
        args.putInt("user_id", userId);
        fragment.setArguments(args);
        requireActivity().getSupportFragmentManager()
                .beginTransaction()
                .replace(R.id.fragment_container, fragment)
                .addToBackStack(null)
                .commit();
    }
}