import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { getAdminCategories } from "@/lib/admin/queries"
import { updateCategoryAction } from "@/lib/admin/actions"

export const dynamic = "force-dynamic"

export default async function AdminCategoriesPage() {
  const categories = await getAdminCategories()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Categories</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Manage visibility, ordering, and featured marketplace + horse categories.
        </p>
      </div>

      {categories.length === 0 ? (
        <Card className="border-border/70">
          <CardContent className="p-8 text-center text-sm text-muted-foreground">
            No categories found.
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-2">
          {categories.map((c) => (
            <Card key={c.id} className="border-border/70">
              <CardContent className="flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-medium text-foreground">{c.name}</span>
                    <Badge variant="secondary">{c.categoryType}</Badge>
                    {!c.isActive && <Badge variant="secondary">hidden</Badge>}
                    {c.featured && <Badge className="bg-accent text-accent-foreground">featured</Badge>}
                  </div>
                  <p className="mt-1 text-xs text-muted-foreground">/{c.slug}</p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <form action={updateCategoryAction} className="flex items-center gap-1">
                    <input type="hidden" name="id" value={c.id} />
                    <input type="hidden" name="field" value="sort_order" />
                    <Input
                      name="value"
                      defaultValue={c.sortOrder}
                      inputMode="numeric"
                      className="h-9 w-16"
                      aria-label="Sort order"
                    />
                    <Button type="submit" size="sm" variant="outline">
                      Order
                    </Button>
                  </form>
                  <form action={updateCategoryAction}>
                    <input type="hidden" name="id" value={c.id} />
                    <input type="hidden" name="field" value="is_active" />
                    <input type="hidden" name="value" value={(!c.isActive).toString()} />
                    <Button type="submit" size="sm" variant="outline">
                      {c.isActive ? "Hide" : "Show"}
                    </Button>
                  </form>
                  <form action={updateCategoryAction}>
                    <input type="hidden" name="id" value={c.id} />
                    <input type="hidden" name="field" value="featured" />
                    <input type="hidden" name="value" value={(!c.featured).toString()} />
                    <Button type="submit" size="sm" variant="ghost">
                      {c.featured ? "Unfeature" : "Feature"}
                    </Button>
                  </form>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
