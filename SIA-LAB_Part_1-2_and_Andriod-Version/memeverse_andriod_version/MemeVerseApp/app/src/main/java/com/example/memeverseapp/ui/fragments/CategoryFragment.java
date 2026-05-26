package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
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

public class CategoryFragment extends Fragment implements
        PostAdapter.OnVoteClickListener,
        PostAdapter.OnCommentClickListener,
        PostAdapter.OnProfileClickListener {

    private String slug;
    private RecyclerView recyclerView;
    private PostAdapter adapter;
    private List<Post> posts = new ArrayList<>();
    private ApiService apiService;
    private PreferencesManager prefManager;
    private int currentPage = 1;
    private boolean isLoading = false;
    private boolean hasMore = true;

    public static CategoryFragment newInstance(String slug) {
        CategoryFragment fragment = new CategoryFragment();
        Bundle args = new Bundle();
        args.putString("slug", slug);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            slug = getArguments().getString("slug");
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_category, container, false);

        recyclerView = view.findViewById(R.id.recyclerView);

        apiService = RetrofitClient.getClient().create(ApiService.class);
        prefManager = new PreferencesManager(requireContext());

        adapter = new PostAdapter(posts, this, this, this);
        recyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
        recyclerView.setAdapter(adapter);

        loadPosts();

        return view;
    }

    private void loadPosts() {
        isLoading = true;

        apiService.getPosts(currentPage, 10).enqueue(new Callback<List<Post>>() {
            @Override
            public void onResponse(Call<List<Post>> call, Response<List<Post>> response) {
                isLoading = false;
                if (response.isSuccessful() && response.body() != null) {
                    List<Post> newPosts = response.body();
                    if (newPosts.isEmpty()) {
                        hasMore = false;
                    } else {
                        // Filter by category if needed
                        for (Post post : newPosts) {
                            if (slug == null || post.getCategory_slug() != null && post.getCategory_slug().equals(slug)) {
                                posts.add(post);
                            }
                        }
                        adapter.notifyDataSetChanged();
                        currentPage++;
                    }
                } else {
                    ToastUtils.showError(getContext(), "Failed to load posts");
                }
            }
            @Override
            public void onFailure(Call<List<Post>> call, Throwable t) {
                isLoading = false;
                ToastUtils.showError(getContext(), "Network error");
            }
        });
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
                    ToastUtils.showSuccess(getContext(), "Vote recorded!");
                } else {
                    ToastUtils.showError(getContext(), "Vote failed");
                }
            }
            @Override
            public void onFailure(Call<VoteResponse> call, Throwable t) {
                ToastUtils.showError(getContext(), "Network error");
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